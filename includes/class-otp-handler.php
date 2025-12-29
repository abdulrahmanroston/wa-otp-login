<?php
/**
 * OTP Handler
 * Handles OTP generation, storage, validation, and WhatsApp sending
 */

if (!defined('ABSPATH')) {
    exit;
}

class WA_OTP_Handler {
    
    private static $instance = null;
    
    /**
     * ==========================================
     * WhatsApp Message Templates - Easy to Edit
     * ==========================================
     */
    
    /**
     * Simple OTP message for all users
     * 
     * Available variables:
     * {otp} - Verification code (3 digits)
     * {site_name} - Website name
     * {expiry} - Expiry time in minutes
     */
    private static function get_otp_message() {
        return "Your verification code: *{otp}*
            Valid for {expiry} minutes.";
    }
    
    /**
     * Alternative: Even shorter message
     * Uncomment this and comment the above if you want ultra-short
     */
    /*
    private static function get_otp_message() {
        return "Verification code: *{otp}*";
    }
    */
    
    /**
     * ==========================================
     * End of Message Templates
     * ==========================================
     */
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // AJAX handlers for frontend
        add_action('wp_ajax_wa_otp_send', array($this, 'ajax_send_otp'));
        add_action('wp_ajax_nopriv_wa_otp_send', array($this, 'ajax_send_otp'));
        
        add_action('wp_ajax_wa_otp_verify', array($this, 'ajax_verify_otp'));
        add_action('wp_ajax_nopriv_wa_otp_verify', array($this, 'ajax_verify_otp'));
        
        // Add handler for immediate queue processing
        add_action('wp_ajax_wa_process_queue_now', array($this, 'trigger_queue_processing'));
        add_action('wp_ajax_nopriv_wa_process_queue_now', array($this, 'trigger_queue_processing'));
    }
    
    /**
     * Generate 3-digit OTP code
     */
    private function generate_otp() {
        // Always 3 digits: 100-999
        return sprintf('%03d', wp_rand(100, 999));
    }
    
    /**
     * Store OTP in transient (temporary storage)
     */
    private function store_otp($phone, $otp, $user_data = array()) {
        $expiry = (int) WA_OTP_Login::get_option('otp_expiry', 300); // 5 minutes default
        
        $data = array(
            'otp' => $otp,
            'attempts' => 0,
            'user_data' => $user_data,
            'created_at' => time(),
        );
        
        set_transient('wa_otp_' . md5($phone), $data, $expiry);
        
        return true;
    }
    
    /**
     * Get stored OTP data
     */
    private function get_otp_data($phone) {
        return get_transient('wa_otp_' . md5($phone));
    }
    
    /**
     * Delete OTP data
     */
    private function delete_otp($phone) {
        delete_transient('wa_otp_' . md5($phone));
    }
    
    /**
     * Check rate limiting (max 3 requests per hour)
     */
    private function check_rate_limit($phone) {
        $limit_key = 'wa_otp_limit_' . md5($phone);
        
        wp_cache_delete($limit_key, 'default');
        
        $attempts = get_transient($limit_key);
        
        // Get max attempts from settings
        $max_attempts = WA_OTP_Login::get_option('max_attempts', 3);
        
        error_log('WA OTP Rate Limit Check: Phone=' . $phone . ', Attempts=' . ($attempts ? $attempts : '0') . ', Max=' . $max_attempts);
        
        if ($attempts && $attempts >= $max_attempts) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Increment rate limit counter
     */
    private function increment_rate_limit($phone) {
        $limit_key = 'wa_otp_limit_' . md5($phone);
        $attempts = get_transient($limit_key);
        
        if (!$attempts) {
            $attempts = 0;
        }
        
        $attempts++;
        set_transient($limit_key, $attempts, 3600); // 1 hour
    }
    
    /**
     * Build simple WhatsApp message
     */
    private function build_message($otp) {
        $site_name = get_bloginfo('name');
        $expiry_minutes = (int) WA_OTP_Login::get_option('otp_expiry', 300) / 60;
        
        $template = self::get_otp_message();
        
        $message = str_replace(
            array('{otp}', '{site_name}', '{expiry}'),
            array($otp, $site_name, $expiry_minutes),
            $template
        );
        
        return $message;
    }
    
    /**
     * AJAX: Send OTP
     */
    public function ajax_send_otp() {
        // Verify nonce
        if (!check_ajax_referer('wa_otp_nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => 'Security verification failed. Please refresh the page.',
            ));
        }
        
        // Get and validate input
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $country_code = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : 'EG';
        
        if (empty($phone)) {
            wp_send_json_error(array(
                'message' => 'Please enter your phone number.',
            ));
        }
        
        // Format phone number
        $formatted_phone = WA_OTP_Phone_Handler::format($phone, $country_code);
        
        if (!$formatted_phone) {
            wp_send_json_error(array(
                'message' => 'Invalid phone number format. Please check and try again.',
            ));
        }
        
        if (!$this->check_rate_limit($formatted_phone)) {
            $max_attempts = WA_OTP_Login::get_option('max_attempts', 3);
            
            // Get remaining time
            $limit_key = 'wa_otp_limit_' . md5($formatted_phone);
            $timeout = get_option('_transient_timeout_' . $limit_key);
            
            $remaining_seconds = 0;
            $time_message = '';
            
            if ($timeout) {
                $remaining_seconds = $timeout - time();
                
                if ($remaining_seconds > 0) {
                    $minutes = floor($remaining_seconds / 60);
                    $seconds = $remaining_seconds % 60;
                    
                    if ($minutes > 0) {
                        $time_message = $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' and ' . $seconds . ' second' . ($seconds != 1 ? 's' : '');
                    } else {
                        $time_message = $seconds . ' second' . ($seconds != 1 ? 's' : '');
                    }
                }
            }
            
            $message = '<strong>Too Many Attempts</strong><br>';
            $message .= 'You have exceeded the maximum of ' . $max_attempts . ' attempts per hour.<br><br>';
            
            if ($time_message) {
                $message .= '⏰ <strong>Try again in:</strong> ' . $time_message . '<br><br>';
            }
            
            $message .= '📱 <strong>Need help?</strong><br>';
            $message .= 'Contact us directly on WhatsApp: <br>';
            $message .= '<a href="https://wa.me/201280188888" target="_blank" style="color: #25D366; font-weight: bold; text-decoration: none;">+20 12 80188888</a>';
            
            wp_send_json_error(array(
                'message' => $message,
            ));
        }
        
        // Check if user exists
        $existing_user = WA_OTP_User_Handler::find_user_by_phone($formatted_phone, $country_code);
        
        // Find existing orders (for new users)
        $existing_orders = array();
        
        if (!$existing_user) {
            $existing_orders = WA_OTP_User_Handler::find_orders_by_phone($formatted_phone, $country_code);
        }
        
        // Generate 3-digit OTP
        $otp = $this->generate_otp();
        
        // Store OTP with user data
        $this->store_otp($formatted_phone, $otp, array(
            'country_code' => $country_code,
            'existing_user' => $existing_user ? $existing_user->ID : false,
            'existing_orders' => $existing_orders,
        ));
        
        // Build simple WhatsApp message
        $message = $this->build_message($otp);
        
        // Send via WA Simple Queue
        if (!function_exists('wa_send')) {
            wp_send_json_error(array(
                'message' => 'WhatsApp service is not configured. Please contact support.',
            ));
        }
        
        $queue_id = wa_send($formatted_phone, $message, array(
            'priority' => 'urgent',
            'metadata' => array(
                'type' => 'otp_login',
                'phone' => $formatted_phone,
            ),
        ));

        if ($queue_id) {
            // Trigger immediate processing (non-blocking)
            wp_remote_post(admin_url('admin-ajax.php'), array(
                'timeout' => 0.01,
                'blocking' => false,
                'body' => array(
                    'action' => 'wa_process_queue_now',
                ),
            ));
        }
        
        if (!$queue_id) {
            wp_send_json_error(array(
                'message' => 'Failed to send verification code. Please try again.',
            ));
        }
        
        // Increment rate limit
        $this->increment_rate_limit($formatted_phone);
        
        // Success response
        wp_send_json_success(array(
            'message' => 'Verification code sent to your WhatsApp!',
        ));
    }
    
    /**
     * AJAX: Verify OTP
     */
    public function ajax_verify_otp() {
        // Verify nonce
        if (!check_ajax_referer('wa_otp_nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => 'Security verification failed. Please refresh the page.',
            ));
        }
        
        // Get and validate input
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $otp_input = isset($_POST['otp']) ? sanitize_text_field($_POST['otp']) : '';
        $country_code = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : 'EG';
        
        if (empty($phone) || empty($otp_input)) {
            wp_send_json_error(array(
                'message' => 'Please enter the verification code.',
            ));
        }
        
        // Format phone number
        $formatted_phone = WA_OTP_Phone_Handler::format($phone, $country_code);
        
        if (!$formatted_phone) {
            wp_send_json_error(array(
                'message' => 'Invalid phone number.',
            ));
        }
        
        // Get stored OTP data
        $otp_data = $this->get_otp_data($formatted_phone);
        
        if (!$otp_data) {
            wp_send_json_error(array(
                'message' => 'Verification code expired. Please request a new code.',
            ));
        }
        
        // Check max attempts (3 attempts)
        if ($otp_data['attempts'] >= 3) {
            $this->delete_otp($formatted_phone);
            wp_send_json_error(array(
                'message' => 'Too many failed attempts. Please request a new code.',
            ));
        }
        
        // Verify OTP
        if ($otp_data['otp'] !== $otp_input) {
            // Increment attempts
            $otp_data['attempts']++;
            set_transient('wa_otp_' . md5($formatted_phone), $otp_data, 300);
            
            $remaining = 3 - $otp_data['attempts'];
            wp_send_json_error(array(
                'message' => "Invalid code. {$remaining} attempts remaining.",
            ));
        }
        
        // OTP is correct - Process login/registration
        $user_data = $otp_data['user_data'];
        $user = null;
        $is_new_user = false;
        $linked_orders = 0;
        
        if ($user_data['existing_user']) {
            // Existing user - just login
            $user = get_user_by('ID', $user_data['existing_user']);
        } else {
            // New user - create account
            $user = WA_OTP_User_Handler::create_user($formatted_phone, $country_code, $user_data['existing_orders']);
            $is_new_user = true;
            
            // Link existing orders
            if ($user && !empty($user_data['existing_orders'])) {
                $linked_orders = WA_OTP_User_Handler::link_orders_to_user($user->ID, $user_data['existing_orders']);
            }
        }
        
        if (!$user || is_wp_error($user)) {
            wp_send_json_error(array(
                'message' => 'Login failed. Please try again.',
            ));
        }
        
        // Login user
        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        // Delete OTP
        $this->delete_otp($formatted_phone);
        
        // Prepare success message
        $message = $is_new_user ? 'Account created successfully!' : 'Welcome back!';
        if ($linked_orders > 0) {
            $message .= " {$linked_orders} orders linked.";
        }
        
        // Determine redirect URL
        $redirect_url = home_url('/');
        
        // Check for redirect_to parameter in URL
        if (isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])) {
            $redirect_url = esc_url_raw($_GET['redirect_to']);
        } 
        // Check if WooCommerce is active
        elseif (class_exists('WooCommerce')) {
            // If coming from checkout, redirect back to checkout
            if (function_exists('wc_get_checkout_url')) {
                $redirect_url = wc_get_checkout_url();
            }
            // Otherwise redirect to my account
            elseif (function_exists('wc_get_page_permalink')) {
                $redirect_url = wc_get_page_permalink('myaccount');
            }
        }
        
        // Allow filtering the redirect URL
        $redirect_url = apply_filters('wa_otp_redirect_url', $redirect_url, $user, $is_new_user);
        
        // Success response
        wp_send_json_success(array(
            'message' => $message,
            'redirect' => $redirect_url,
            'is_new_user' => $is_new_user,
            'linked_orders' => $linked_orders,
        ));
    }

    /**
     * Trigger immediate queue processing
     */
    public function trigger_queue_processing() {
        if (class_exists('WA_Queue')) {
            WA_Queue::instance()->process();
        }
        wp_die();
    }
}
