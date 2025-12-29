<?php
/**
 * Frontend Handler
 * Handles shortcode, form display, and asset loading
 * Shortcode : [wa_otp_login]
 */

if (!defined('ABSPATH')) {
    exit;
}

class WA_OTP_Frontend {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Register shortcode
        add_shortcode('wa_otp_login', array($this, 'render_login_form'));
        
        // Enqueue assets only when needed
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        
        // Redirect to login if not logged in on checkout
        add_action('template_redirect', array($this, 'redirect_checkout_to_login'));

        // Force load assets in footer for dynamic content
        add_action('wp_footer', array($this, 'ensure_assets_loaded'), 999);
        
        // Auto-display on login page (if enabled)
        $auto_display = WA_OTP_Login::get_option('auto_display_on_login', true);
        if ($auto_display) {
            add_action('woocommerce_login_form_start', array($this, 'display_on_woo_login'), 5);
            // Removed wp-login.php hook to prevent showing on admin login page
        }
    }

    /**
     * Ensure assets are loaded in footer (for dynamic content)
     */
    public function ensure_assets_loaded() {
        // Check if shortcode was used or login form was displayed
        global $post;
        
        $should_load = false;
        
        // Check if shortcode exists in content
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'wa_otp_login')) {
            $should_load = true;
        }
        
        // Check if on checkout or account page
        if (function_exists('is_checkout') && is_checkout()) {
            $should_load = true;
        }
        
        if (function_exists('is_account_page') && is_account_page()) {
            $should_load = true;
        }
        
        // Check if login form was auto-displayed
        if (did_action('woocommerce_login_form_start')) {
            $should_load = true;
        }
        
        // Force enqueue if needed
        if ($should_load) {
            if (!wp_style_is('wa-otp-login', 'enqueued')) {
                wp_enqueue_style('wa-otp-login');
            }
            
            if (!wp_script_is('wa-otp-login', 'enqueued')) {
                wp_enqueue_script('wa-otp-login');
            }
            
            // Print scripts if not already printed
            if (!wp_script_is('wa-otp-login', 'done')) {
                wp_print_scripts('wa-otp-login');
            }
            
            // Print styles if not already printed
            if (!wp_style_is('wa-otp-login', 'done')) {
                wp_print_styles('wa-otp-login');
            }
        }
    }
    
    /**
     * Register and enqueue assets
     */
    public function register_assets() {
        // Register styles
        wp_register_style(
            'wa-otp-login',
            WA_OTP_URL . 'assets/css/frontend.css',
            array(),
            WA_OTP_VERSION
        );
        
        // Register scripts
        wp_register_script(
            'wa-otp-login',
            WA_OTP_URL . 'assets/js/frontend.js',
            array('jquery'),
            WA_OTP_VERSION,
            true
        );
        
        // Localize script with AJAX data
        wp_localize_script('wa-otp-login', 'waOtpData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wa_otp_nonce'),
            'defaultCountry' => WA_OTP_Login::get_option('default_country', 'EG'),
        ));
        
        // Force enqueue on checkout and my-account pages
        if (function_exists('is_checkout') && is_checkout()) {
            wp_enqueue_style('wa-otp-login');
            wp_enqueue_script('wa-otp-login');
        }
        
        if (function_exists('is_account_page') && is_account_page()) {
            wp_enqueue_style('wa-otp-login');
            wp_enqueue_script('wa-otp-login');
        }
    }
    
    /**
     * Enqueue assets when shortcode is rendered
     */
    private function enqueue_assets() {
        wp_enqueue_style('wa-otp-login');
        wp_enqueue_script('wa-otp-login');
    }
    
    /**
     * Shortcode: [wa_otp_login]
     * 
     * Usage: [wa_otp_login redirect="/my-account"]
     */
    public function render_login_form($atts) {
        // Don't show for logged-in users
        if (is_user_logged_in()) {
            return '<div class="wa-otp-notice">You are already logged in.</div>';
        }
        
        // Parse attributes
        $atts = shortcode_atts(array(
            'redirect' => home_url('/'),
            'title' => 'Login with WhatsApp',
            'show_title' => 'yes',
        ), $atts);
        
        // Enqueue assets
        $this->enqueue_assets();
        
        // Get countries
        $countries = WA_OTP_Phone_Handler::get_countries_by_region();
        $all_countries = WA_OTP_Phone_Handler::get_countries();
        $default_country = WA_OTP_Login::get_option('default_country', 'EG');
        
        // Start output buffering
        ob_start();
        ?>
        
        <div class="wa-otp-container">
            
            <?php if ($atts['show_title'] === 'yes'): ?>
                <h3 class="wa-otp-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <!-- Phone Number Step -->
            <div id="wa-otp-phone-step" class="wa-otp-step">
                
                <div class="wa-otp-field-group">
                    <label for="wa-otp-country">Country</label>
                    <select id="wa-otp-country" class="wa-otp-select">
                        <?php foreach ($countries as $region => $country_codes): ?>
                            <optgroup label="<?php echo esc_attr($region); ?>">
                                <?php foreach ($country_codes as $code): ?>
                                    <?php if (isset($all_countries[$code])): ?>
                                        <?php $country = $all_countries[$code]; ?>
                                        <option 
                                            value="<?php echo esc_attr($code); ?>" 
                                            data-code="<?php echo esc_attr($country['code']); ?>"
                                            <?php selected($code, $default_country); ?>
                                        >
                                            <?php echo esc_html($country['flag'] . ' ' . $country['name'] . ' (+' . $country['code'] . ')'); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="wa-otp-field-group">
                    <label for="wa-otp-phone">Phone Number</label>
                    <div class="wa-otp-phone-input">
                        <span class="wa-otp-country-code">+<?php echo esc_html($all_countries[$default_country]['code']); ?></span>
                        <input 
                            type="tel" 
                            id="wa-otp-phone" 
                            class="wa-otp-input"
                            placeholder="1234567890"
                            autocomplete="tel"
                            maxlength="15"
                        >
                    </div>
                    <small class="wa-otp-hint">Enter your WhatsApp number without country code</small>
                </div>
                
                <button type="button" id="wa-otp-send-btn" class="wa-otp-button">
                    <span class="wa-otp-btn-text">Send Verification Code</span>
                    <span class="wa-otp-btn-loading" style="display:none;">Sending...</span>
                </button>
                
            </div>
            
            <!-- OTP Verification Step -->
            <div id="wa-otp-verify-step" class="wa-otp-step" style="display:none;">
                
                <p class="wa-otp-info">Enter the 3-digit code sent to your WhatsApp</p>
                
                <div class="wa-otp-field-group">
                    <label for="wa-otp-code">Verification Code</label>
                    <input 
                        type="text" 
                        id="wa-otp-code" 
                        class="wa-otp-input wa-otp-code-input"
                        placeholder="000"
                        maxlength="3"
                        autocomplete="one-time-code"
                        inputmode="numeric"
                        pattern="[0-9]*"
                    >
                </div>
                
                <button type="button" id="wa-otp-verify-btn" class="wa-otp-button wa-otp-button-primary">
                    <span class="wa-otp-btn-text">Verify & Login</span>
                    <span class="wa-otp-btn-loading" style="display:none;">Verifying...</span>
                </button>
                
                <div class="wa-otp-actions">
                    <button type="button" id="wa-otp-resend-btn" class="wa-otp-link-button">
                        Resend Code
                    </button>
                    <button type="button" id="wa-otp-back-btn" class="wa-otp-link-button">
                        Change Number
                    </button>
                </div>
                
            </div>
            
            <!-- Message Display -->
            <div id="wa-otp-message" class="wa-otp-message" style="display:none;"></div>
            
        </div>
        
        <?php
        return ob_get_clean();
    }

    /**
     * Display on WooCommerce login page
     */
    public function display_on_woo_login() {
        if (is_user_logged_in()) {
            return;
        }
        
        echo '<div class="wa-otp-woo-wrapper">';
        echo $this->render_login_form(array('show_title' => 'yes', 'title' => 'Quick Login with WhatsApp'));
        echo '<div class="wa-otp-separator"><span>OR</span></div>';
        echo '</div>';
        
        // Add separator styles
        echo '<style>
            .wa-otp-woo-wrapper {
                margin-bottom: 30px;
            }
            .wa-otp-separator {
                position: relative;
                text-align: center;
                margin: 30px 0;
            }
            .wa-otp-separator:before {
                content: "";
                position: absolute;
                top: 50%;
                left: 0;
                right: 0;
                height: 1px;
                background: linear-gradient(to right, transparent, #ddd, transparent);
            }
            .wa-otp-separator span {
                position: relative;
                display: inline-block;
                background: #fff;
                padding: 0 20px;
                color: #666;
                font-weight: 500;
                font-size: 14px;
            }
        </style>';
    }

    /**
     * Redirect non-logged-in users from checkout to my-account with redirect parameter
     */
    public function redirect_checkout_to_login() {
        // Only on checkout page
        if (!function_exists('is_checkout') || !is_checkout()) {
            return;
        }
        
        // Only if not logged in
        if (is_user_logged_in()) {
            return;
        }
        
        // Don't redirect on order-received page (thank you page)
        if (is_wc_endpoint_url('order-received')) {
            return;
        }
        
        // Get checkout URL to redirect back after login
        $checkout_url = wc_get_checkout_url();
        
        // My account URL with redirect parameter
        $myaccount_url = add_query_arg('redirect_to', urlencode($checkout_url), wc_get_page_permalink('myaccount'));
        
        // Add notice
        wc_add_notice('Please login with WhatsApp to complete your order.', 'notice');
        
        // Redirect
        wp_safe_redirect($myaccount_url);
        exit;
    }
}
