<?php
/**
 * User Handler
 * Handles user creation, phone number storage, and order linking
 */

if (!defined('ABSPATH')) {
    exit;
}

class WA_OTP_User_Handler {
    
    private static $instance = null;
    private static $table_name = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'wa_otp_phones';
    }
    
    /**
     * Create database table for phone numbers
     */
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wa_otp_phones';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            phone VARCHAR(20) NOT NULL,
            country_code VARCHAR(5) NOT NULL,
            phone_variants TEXT,
            is_verified TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_phone (phone),
            INDEX idx_user_id (user_id),
            INDEX idx_phone (phone),
            INDEX idx_country (country_code)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Find user by phone number (ENHANCED - search all variants)
     */
    public static function find_user_by_phone($phone, $country_code = 'EG') {
        global $wpdb;
        
        $variants = WA_OTP_Phone_Handler::get_variants($phone, $country_code);
        
        if (empty($variants)) {
            error_log('WA OTP: No variants generated for phone: ' . $phone);
            return false;
        }
        
        error_log('WA OTP: Searching for user with variants: ' . print_r($variants, true));
        
        $table = $wpdb->prefix . 'wa_otp_phones';
        
        // Method 1: Search in custom phone table (all variants)
        foreach ($variants as $variant) {
            $user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM $table WHERE phone = %s AND is_verified = 1 LIMIT 1",
                $variant
            ));
            
            if ($user_id) {
                $user = get_user_by('ID', $user_id);
                if ($user) {
                    error_log('WA OTP: Found user in phone table - ID: ' . $user_id);
                    return $user;
                }
            }
        }
        
        // Method 2: Search in WooCommerce billing_phone meta (all variants)
        if (class_exists('WooCommerce')) {
            foreach ($variants as $variant) {
                $query = $wpdb->prepare(
                    "SELECT DISTINCT u.ID
                    FROM {$wpdb->users} u
                    INNER JOIN {$wpdb->usermeta} um ON (u.ID = um.user_id)
                    WHERE um.meta_key = 'billing_phone' 
                    AND um.meta_value = %s
                    LIMIT 1",
                    $variant
                );
                
                $user_id = $wpdb->get_var($query);
                
                if ($user_id) {
                    $user = get_user_by('ID', $user_id);
                    
                    if ($user) {
                        error_log('WA OTP: Found user in billing_phone meta - ID: ' . $user_id . ', variant: ' . $variant);
                        
                        // Save to phone table for faster future lookups
                        self::save_user_phone($user_id, $phone, $country_code);
                        
                        return $user;
                    }
                }
            }
        }
        
        // Method 3: Search in order billing phones (all variants)
        if (class_exists('WooCommerce')) {
            foreach ($variants as $variant) {
                $order_query = $wpdb->prepare(
                    "SELECT DISTINCT pm2.meta_value as customer_id
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm1 ON (p.ID = pm1.post_id AND pm1.meta_key = '_billing_phone' AND pm1.meta_value = %s)
                    INNER JOIN {$wpdb->postmeta} pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = '_customer_user')
                    WHERE p.post_type = 'shop_order'
                    AND pm2.meta_value > 0
                    ORDER BY p.post_date DESC
                    LIMIT 1",
                    $variant
                );
                
                $customer_id = $wpdb->get_var($order_query);
                
                if ($customer_id && $customer_id > 0) {
                    $user = get_user_by('ID', $customer_id);
                    
                    if ($user) {
                        error_log('WA OTP: Found user from order customer_id - ID: ' . $customer_id . ', variant: ' . $variant);
                        
                        // Save to phone table
                        self::save_user_phone($user->ID, $phone, $country_code);
                        
                        return $user;
                    }
                }
            }
        }
        
        error_log('WA OTP: No user found for phone: ' . $phone);
        return false;
    }
    
    /**
     * Find orders by phone number (WooCommerce)
     */
    public static function find_orders_by_phone($phone, $country_code = 'EG') {
        if (!class_exists('WooCommerce')) {
            return array();
        }
        
        global $wpdb;
        
        $variants = WA_OTP_Phone_Handler::get_variants($phone, $country_code);
        
        if (empty($variants)) {
            return array();
        }
        
        $all_orders = array();
        
        foreach ($variants as $variant) {
            $orders = $wpdb->get_results($wpdb->prepare(
                "SELECT DISTINCT 
                    p.ID, 
                    p.post_date,
                    (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_billing_phone') as phone,
                    (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_billing_first_name') as first_name,
                    (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_billing_last_name') as last_name,
                    (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_billing_email') as email,
                    (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_billing_address_1') as address,
                    (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_billing_city') as city,
                    (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_customer_user') as customer_id
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_billing_phone' AND pm.meta_value = %s)
                WHERE p.post_type = 'shop_order'
                AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending')
                ORDER BY p.post_date DESC
                LIMIT 50",
                $variant
            ));
            
            if ($orders) {
                $all_orders = array_merge($all_orders, $orders);
            }
        }
        
        // Remove duplicates by order ID
        $unique_orders = array();
        $seen_ids = array();
        
        foreach ($all_orders as $order) {
            if (!in_array($order->ID, $seen_ids)) {
                $unique_orders[] = $order;
                $seen_ids[] = $order->ID;
            }
        }
        
        return $unique_orders;
    }
    
    /**
     * Create new user with phone number
     */
    public static function create_user($phone, $country_code = 'EG', $orders = array()) {
        // Generate unique username
        $phone_clean = preg_replace('/[^0-9]/', '', $phone);
        $username = 'user_' . substr($phone_clean, -8) . '_' . wp_rand(100, 999);
        
        while (username_exists($username)) {
            $username = 'user_' . substr($phone_clean, -8) . '_' . wp_rand(100, 999);
        }
        
        // Get customer data from first order if available
        $customer_data = array(
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'address' => '',
            'city' => '',
        );
        
        if (!empty($orders)) {
            $first_order = $orders[0];
            $customer_data = array(
                'first_name' => isset($first_order->first_name) ? $first_order->first_name : '',
                'last_name' => isset($first_order->last_name) ? $first_order->last_name : '',
                'email' => isset($first_order->email) ? $first_order->email : '',
                'address' => isset($first_order->address) ? $first_order->address : '',
                'city' => isset($first_order->city) ? $first_order->city : '',
            );
        }
        
        // Generate email
        $email = '';
        if (!empty($customer_data['email']) && is_email($customer_data['email']) && !email_exists($customer_data['email'])) {
            $email = $customer_data['email'];
        } else {
            $email = $username . '@temp.local';
        }
        
        // Generate display name
        $display_name = 'Customer ' . substr($phone_clean, -4);
        if (!empty($customer_data['first_name'])) {
            $display_name = trim($customer_data['first_name'] . ' ' . $customer_data['last_name']);
        }
        
        // Create user
        $user_data = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => wp_generate_password(16, true, true),
            'display_name' => $display_name,
            'first_name' => $customer_data['first_name'],
            'last_name' => $customer_data['last_name'],
            'role' => 'customer',
        );
        
        $user_id = wp_insert_user($user_data);
        
        if (is_wp_error($user_id)) {
            error_log('WA OTP Login: User creation failed - ' . $user_id->get_error_message());
            return false;
        }
        
        error_log('WA OTP: Created new user - ID: ' . $user_id);
        
        // Save phone number
        self::save_user_phone($user_id, $phone, $country_code);
        
        // Save billing information
        update_user_meta($user_id, 'billing_phone', $phone);
        update_user_meta($user_id, 'billing_first_name', $customer_data['first_name']);
        update_user_meta($user_id, 'billing_last_name', $customer_data['last_name']);
        update_user_meta($user_id, 'billing_email', $customer_data['email']);
        update_user_meta($user_id, 'billing_address_1', $customer_data['address']);
        update_user_meta($user_id, 'billing_city', $customer_data['city']);
        update_user_meta($user_id, 'billing_country', $country_code);
        
        // Copy to shipping
        update_user_meta($user_id, 'shipping_first_name', $customer_data['first_name']);
        update_user_meta($user_id, 'shipping_last_name', $customer_data['last_name']);
        update_user_meta($user_id, 'shipping_address_1', $customer_data['address']);
        update_user_meta($user_id, 'shipping_city', $customer_data['city']);
        update_user_meta($user_id, 'shipping_country', $country_code);
        
        return get_user_by('ID', $user_id);
    }
    
    /**
     * Save user phone number to custom table
     */
    public static function save_user_phone($user_id, $phone, $country_code = 'EG') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wa_otp_phones';
        $formatted_phone = WA_OTP_Phone_Handler::format($phone, $country_code);
        $variants = WA_OTP_Phone_Handler::get_variants($phone, $country_code);
        
        if (!$formatted_phone) {
            return false;
        }
        
        // Check if exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE phone = %s",
            $formatted_phone
        ));
        
        if ($exists) {
            // Update existing
            return $wpdb->update(
                $table,
                array(
                    'user_id' => $user_id,
                    'phone_variants' => wp_json_encode($variants),
                    'is_verified' => 1,
                ),
                array('phone' => $formatted_phone),
                array('%d', '%s', '%d'),
                array('%s')
            );
        } else {
            // Insert new
            return $wpdb->insert(
                $table,
                array(
                    'user_id' => $user_id,
                    'phone' => $formatted_phone,
                    'country_code' => $country_code,
                    'phone_variants' => wp_json_encode($variants),
                    'is_verified' => 1,
                ),
                array('%d', '%s', '%s', '%s', '%d')
            );
        }
    }
    
    /**
     * Link orders to user
     */
    public static function link_orders_to_user($user_id, $orders) {
        if (empty($orders) || !class_exists('WooCommerce')) {
            return 0;
        }
        
        global $wpdb;
        $linked = 0;
        
        foreach ($orders as $order) {
            // Skip if already linked to this user
            if ((int) $order->customer_id === (int) $user_id) {
                continue;
            }
            
            // Update order customer
            $updated = update_post_meta($order->ID, '_customer_user', $user_id);
            
            if ($updated) {
                $linked++;
            }
        }
        
        if ($linked > 0) {
            // Clear caches
            clean_user_cache($user_id);
            wp_cache_flush();
        }
        
        return $linked;
    }
    
    /**
     * Get user's phone number
     */
    public static function get_user_phone($user_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wa_otp_phones';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT phone, country_code FROM $table WHERE user_id = %d AND is_verified = 1 LIMIT 1",
            $user_id
        ));
    }
}
