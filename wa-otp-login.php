<?php
/**
 * Plugin Name: WA OTP Login
 * Plugin URI: https://abdulrahmanroston.com
 * Description: Professional WhatsApp OTP login system with WA Simple Queue integration
 * Version: 2.0.1
 * Author: Abdulrahman Roston
 * Author URI: https://abdulrahmanroston.com
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * Text Domain: wa-otp-login
 * Domain Path: /languages
 * shorcode : [wa_otp_login]
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('WA_OTP_VERSION', '2.0.1');
define('WA_OTP_PATH', plugin_dir_path(__FILE__));
define('WA_OTP_URL', plugin_dir_url(__FILE__));
define('WA_OTP_BASENAME', plugin_basename(__FILE__));


// ==================== Plugin Update Checker ====================

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Setup automatic updates from GitHub commits
 */
if ( file_exists( WA_OTP_PATH . 'includes/plugin-update-checker-master/plugin-update-checker.php' ) ) {
    require WA_OTP_PATH . 'includes/plugin-update-checker-master/plugin-update-checker.php';
    
    $waOtpUpdateChecker = PucFactory::buildUpdateChecker(
        'https://github.com/abdulrahmanroston/wa-otp-login/',
        __FILE__,
        'wa-otp-login'
    );
    
    // Monitor the main branch for updates directly from commits
    $waOtpUpdateChecker->setBranch( 'main' );
}



/**
 * Main Plugin Class
 */
final class WA_OTP_Login {
    
    private static $instance = null;
    private $dependencies_met = false;
    
    /**
     * Singleton instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Check dependencies and initialize after all plugins loaded
        add_action('plugins_loaded', array($this, 'check_and_init'), 20); // Priority 20 - after WA Simple loads
        add_action('init', array($this, 'load_textdomain'));
    }
    
    /**
     * Check dependencies and initialize
     */
    public function check_and_init() {
        // Check if WA Simple Queue is loaded
        if (class_exists('WA_Simple') && function_exists('wa_send')) {
            $this->dependencies_met = true;
            $this->load_files();
            $this->init();
        } else {
            // Show admin notice
            add_action('admin_notices', array($this, 'dependency_notice'));
        }
    }
    
    /**
     * Show dependency notice
     */
    public function dependency_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <strong>WA OTP Login:</strong> 
                This plugin requires "WA Simple Queue" plugin to be installed and activated.
            </p>
            <p>
                <em>Please install and activate WA Simple Queue plugin first.</em>
            </p>
        </div>
        <?php
    }
    
    /**
     * Load required files
     */
    private function load_files() {
        require_once WA_OTP_PATH . 'includes/class-phone-handler.php';
        require_once WA_OTP_PATH . 'includes/class-otp-handler.php';
        require_once WA_OTP_PATH . 'includes/class-user-handler.php';
        require_once WA_OTP_PATH . 'includes/class-frontend.php';
        
        if (is_admin()) {
            require_once WA_OTP_PATH . 'includes/class-admin.php';
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Check if WA Simple is active before activation
        if (!class_exists('WA_Simple')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                '<h1>Plugin Activation Error</h1>' .
                '<p><strong>WA OTP Login</strong> requires <strong>WA Simple Queue</strong> plugin to be installed and activated.</p>' .
                '<p>Please install WA Simple Queue first, then try activating this plugin again.</p>' .
                '<p><a href="' . admin_url('plugins.php') . '">&laquo; Back to Plugins</a></p>'
            );
        }
        
        // Create database tables
        require_once WA_OTP_PATH . 'includes/class-user-handler.php';
        WA_OTP_User_Handler::create_tables();
        
        // Set default options
        if (!get_option('wa_otp_settings')) {
            update_option('wa_otp_settings', array(
                'default_country' => 'EG',
                'enable_order_linking' => true,
                'otp_expiry' => 300, // 5 minutes
                'max_attempts' => 3,
            ));
        }
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Initialize classes
        WA_OTP_Phone_Handler::instance();
        WA_OTP_Handler::instance();
        WA_OTP_User_Handler::instance();
        WA_OTP_Frontend::instance();
        
        if (is_admin()) {
            WA_OTP_Admin::instance();
        }
    }
    
    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wa-otp-login',
            false,
            dirname(WA_OTP_BASENAME) . '/languages'
        );
    }
    
    /**
     * Get option helper
     */
    public static function get_option($key, $default = '') {
        $options = get_option('wa_otp_settings', array());
        return isset($options[$key]) ? $options[$key] : $default;
    }
    
    /**
     * Update option helper
     */
    public static function update_option($key, $value) {
        $options = get_option('wa_otp_settings', array());
        $options[$key] = $value;
        return update_option('wa_otp_settings', $options);
    }
}

// Initialize plugin
WA_OTP_Login::instance();
