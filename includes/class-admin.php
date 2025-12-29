<?php
/**
 * Admin Handler
 * Handles admin settings page and configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

class WA_OTP_Admin {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        add_filter('plugin_action_links_' . WA_OTP_BASENAME, array($this, 'add_settings_link'));
        add_action('admin_post_wa_otp_clear_limits', array($this, 'clear_rate_limits'));
    }
    
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wa-otp-settings') . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'WA OTP Login',
            'WA OTP Login',
            'manage_options',
            'wa-otp-settings',
            array($this, 'render_settings_page'),
            'dashicons-whatsapp',
            30
        );
        
        add_submenu_page(
            'wa-otp-settings',
            'Settings',
            'Settings',
            'manage_options',
            'wa-otp-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'wa-otp-settings',
            'Phone Numbers',
            'Phone Numbers',
            'manage_options',
            'wa-otp-phones',
            array($this, 'render_phones_page')
        );
    }
    
    public function register_settings() {
        register_setting('wa_otp_settings_group', 'wa_otp_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings'),
        ));
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['default_country'] = isset($input['default_country']) ? sanitize_text_field($input['default_country']) : 'EG';
        $sanitized['enable_order_linking'] = isset($input['enable_order_linking']) ? 1 : 0;
        $sanitized['auto_display_on_login'] = isset($input['auto_display_on_login']) ? 1 : 0;
        $sanitized['otp_expiry'] = isset($input['otp_expiry']) ? absint($input['otp_expiry']) : 300;
        $sanitized['max_attempts'] = isset($input['max_attempts']) ? absint($input['max_attempts']) : 3;
        
        return $sanitized;
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wa-otp') === false) {
            return;
        }
        
        wp_enqueue_style('wa-otp-admin', WA_OTP_URL . 'assets/css/admin.css', array(), WA_OTP_VERSION);
    }
    
    public function clear_rate_limits() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_admin_referer('wa_otp_clear_limits');
        
        global $wpdb;
        
        $deleted = $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_wa_otp_limit_%' 
            OR option_name LIKE '_transient_timeout_wa_otp_limit_%'"
        );
        
        wp_cache_flush();
        
        wp_redirect(add_query_arg(array(
            'page' => 'wa-otp-settings',
            'limits_cleared' => $deleted,
        ), admin_url('admin.php')));
        exit;
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (isset($_POST['wa_otp_save_settings']) && check_admin_referer('wa_otp_settings_nonce')) {
            update_option('wa_otp_settings', $this->sanitize_settings($_POST));
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        if (isset($_GET['limits_cleared'])) {
            $count = absint($_GET['limits_cleared']);
            echo '<div class="notice notice-success"><p><strong>✅ Success!</strong> Cleared ' . $count . ' rate limit records. All users can now try again.</p></div>';
        }
        
        $settings = get_option('wa_otp_settings', array());
        $countries = WA_OTP_Phone_Handler::get_countries();
        ?>
        
        <div class="wrap">
            <h1>WA OTP Login Settings</h1>
            
            <div class="wa-otp-admin-container">
                
                <div class="wa-otp-admin-main">
                    <form method="post" action="">
                        <?php wp_nonce_field('wa_otp_settings_nonce'); ?>
                        
                        <table class="form-table">
                            
                            <tr>
                                <th scope="row">
                                    <label for="default_country">Default Country</label>
                                </th>
                                <td>
                                    <select name="default_country" id="default_country" class="regular-text">
                                        <?php foreach ($countries as $code => $country): ?>
                                            <option 
                                                value="<?php echo esc_attr($code); ?>"
                                                <?php selected(isset($settings['default_country']) ? $settings['default_country'] : 'EG', $code); ?>
                                            >
                                                <?php echo esc_html($country['flag'] . ' ' . $country['name'] . ' (+' . $country['code'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description">Default country for phone number input</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="otp_expiry">OTP Expiry Time</label>
                                </th>
                                <td>
                                    <input 
                                        type="number" 
                                        name="otp_expiry" 
                                        id="otp_expiry" 
                                        value="<?php echo esc_attr(isset($settings['otp_expiry']) ? $settings['otp_expiry'] : 300); ?>"
                                        min="60"
                                        max="3600"
                                        class="small-text"
                                    > seconds
                                    <p class="description">How long the OTP code remains valid (default: 300 seconds = 5 minutes)</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="max_attempts">Max Verification Attempts</label>
                                </th>
                                <td>
                                    <input 
                                        type="number" 
                                        name="max_attempts" 
                                        id="max_attempts" 
                                        value="<?php echo esc_attr(isset($settings['max_attempts']) ? $settings['max_attempts'] : 3); ?>"
                                        min="3"
                                        class="small-text"
                                    > attempts
                                    <p class="description">Maximum number of wrong code attempts before requesting new code</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="enable_order_linking">Order Linking</label>
                                </th>
                                <td>
                                    <label>
                                        <input 
                                            type="checkbox" 
                                            name="enable_order_linking" 
                                            id="enable_order_linking" 
                                            value="1"
                                            <?php checked(isset($settings['enable_order_linking']) ? $settings['enable_order_linking'] : 1, 1); ?>
                                        >
                                        Automatically link previous orders to new accounts
                                    </label>
                                    <p class="description">When enabled, orders with matching phone numbers will be linked to newly created accounts</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="auto_display_on_login">Auto Display on Login Pages</label>
                                </th>
                                <td>
                                    <label>
                                        <input 
                                            type="checkbox" 
                                            name="auto_display_on_login" 
                                            id="auto_display_on_login" 
                                            value="1"
                                            <?php checked(isset($settings['auto_display_on_login']) ? $settings['auto_display_on_login'] : 1, 1); ?>
                                        >
                                        Automatically show WhatsApp login on WordPress & WooCommerce login pages
                                    </label>
                                    <p class="description">When enabled, the WhatsApp OTP login form will appear automatically on login pages</p>
                                </td>
                            </tr>
                            
                        </table>
                        
                        <input type="hidden" name="wa_otp_save_settings" value="1">
                        <?php submit_button('Save Settings'); ?>
                        
                    </form>
                </div>
                
                <div class="wa-otp-admin-sidebar">
                    
                    <div class="wa-otp-info-box">
                        <h3>📱 Shortcode Usage</h3>
                        <p>Use this shortcode to display the login form:</p>
                        de>[wa_otp_login]</code>
                        
                        <h4 style="margin-top: 20px;">With custom redirect:</h4>
                        de>[wa_otp_login redirect="/my-account"]</code>
                        
                        <h4 style="margin-top: 20px;">Without title:</h4>
                        de>[wa_otp_login show_title="no"]</code>
                    </div>
                    
                    <div class="wa-otp-info-box">
                        <h3>⚙️ Requirements</h3>
                        <ul style="margin-left: 20px;">
                            <li>✅ WA Simple Queue plugin must be active</li>
                            <li>✅ WhatsApp API configured in WA Queue settings</li>
                            <li>✅ WooCommerce (optional, for order linking)</li>
                        </ul>
                    </div>
                    
                    <div class="wa-otp-info-box">
                        <h3>📊 Statistics</h3>
                        <?php
                        global $wpdb;
                        $table = $wpdb->prefix . 'wa_otp_phones';
                        $total_users = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE is_verified = 1");
                        ?>
                        <p><strong>Registered Phone Numbers:</strong> <?php echo number_format($total_users); ?></p>
                    </div>
                    
                    <div class="wa-otp-info-box" style="border-left: 4px solid #dc3232;">
                        <h3>🔧 Maintenance</h3>
                        <p>Clear all rate limit blocks:</p>
                        <a 
                            href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=wa_otp_clear_limits'), 'wa_otp_clear_limits'); ?>" 
                            class="button button-secondary"
                            onclick="return confirm('Clear all rate limits?');"
                            style="width: 100%; text-align: center; margin-top: 10px; display: block; box-sizing: border-box;"
                        >
                            🗑️ Clear All Rate Limits
                        </a>
                        <p style="margin-top: 10px; font-size: 12px; color: #666;">
                            <em>Use this if users are blocked and can't login.</em>
                        </p>
                    </div>
                    
                </div>
                
            </div>
        </div>
        
        <style>
        .wa-otp-admin-container {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        .wa-otp-admin-main {
            flex: 1;
            background: #fff;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .wa-otp-admin-sidebar {
            width: 300px;
        }
        .wa-otp-info-box {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .wa-otp-info-box h3 {
            margin-top: 0;
            font-size: 16px;
        }
        .wa-otp-info-box code {
            display: block;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            font-size: 13px;
            margin-top: 10px;
        }
        </style>
        
        <?php
    }
    
    public function render_phones_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'wa_otp_phones';
        
        $per_page = 50;
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($paged - 1) * $per_page;
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        
        $records = $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, u.user_login, u.display_name, u.user_email
            FROM $table p
            LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
            ORDER BY p.created_at DESC
            LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
        
        $total_pages = ceil($total / $per_page);
        ?>
        
        <div class="wrap">
            <h1>Registered Phone Numbers</h1>
            
            <p>Total registered phone numbers: <strong><?php echo number_format($total); ?></strong></p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Phone Number</th>
                        <th>Country</th>
                        <th>Status</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($records): ?>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($record->display_name); ?></strong><br>
                                    <small><?php echo esc_html($record->user_login); ?></small><br>
                                    <small><?php echo esc_html($record->user_email); ?></small>
                                </td>
                                <td>de><?php echo esc_html($record->phone); ?></code></td>
                                <td><?php echo esc_html($record->country_code); ?></td>
                                <td>
                                    <?php if ($record->is_verified): ?>
                                        <span style="color: green;">✓ Verified</span>
                                    <?php else: ?>
                                        <span style="color: orange;">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($record->created_at); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $total_pages,
                            'current' => $paged,
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
        
        <?php
    }
}
