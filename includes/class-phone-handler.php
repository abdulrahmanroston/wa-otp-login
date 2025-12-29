<?php
/**
 * Phone Number Handler
 * Handles phone number formatting, validation, and country code management
 */

if (!defined('ABSPATH')) {
    exit;
}

class WA_OTP_Phone_Handler {
    
    private static $instance = null;
    
    /**
     * Supported countries with their codes (50+ countries)
     */
    private static $countries = array(
        // Arab Countries
        'EG' => array('name' => 'Egypt', 'code' => '20', 'format' => '10', 'flag' => '🇪🇬'),
        'SA' => array('name' => 'Saudi Arabia', 'code' => '966', 'format' => '9', 'flag' => '🇸🇦'),
        'AE' => array('name' => 'UAE', 'code' => '971', 'format' => '9', 'flag' => '🇦🇪'),
        'KW' => array('name' => 'Kuwait', 'code' => '965', 'format' => '8', 'flag' => '🇰🇼'),
        'QA' => array('name' => 'Qatar', 'code' => '974', 'format' => '8', 'flag' => '🇶🇦'),
        'OM' => array('name' => 'Oman', 'code' => '968', 'format' => '8', 'flag' => '🇴🇲'),
        'BH' => array('name' => 'Bahrain', 'code' => '973', 'format' => '8', 'flag' => '🇧🇭'),
        'JO' => array('name' => 'Jordan', 'code' => '962', 'format' => '9', 'flag' => '🇯🇴'),
        'LB' => array('name' => 'Lebanon', 'code' => '961', 'format' => '8', 'flag' => '🇱🇧'),
        'IQ' => array('name' => 'Iraq', 'code' => '964', 'format' => '10', 'flag' => '🇮🇶'),
        'PS' => array('name' => 'Palestine', 'code' => '970', 'format' => '9', 'flag' => '🇵🇸'),
        'SY' => array('name' => 'Syria', 'code' => '963', 'format' => '9', 'flag' => '🇸🇾'),
        'LY' => array('name' => 'Libya', 'code' => '218', 'format' => '9', 'flag' => '🇱🇾'),
        'TN' => array('name' => 'Tunisia', 'code' => '216', 'format' => '8', 'flag' => '🇹🇳'),
        'DZ' => array('name' => 'Algeria', 'code' => '213', 'format' => '9', 'flag' => '🇩🇿'),
        'MA' => array('name' => 'Morocco', 'code' => '212', 'format' => '9', 'flag' => '🇲🇦'),
        'SD' => array('name' => 'Sudan', 'code' => '249', 'format' => '9', 'flag' => '🇸🇩'),
        'YE' => array('name' => 'Yemen', 'code' => '967', 'format' => '9', 'flag' => '🇾🇪'),
        
        // Europe
        'GB' => array('name' => 'United Kingdom', 'code' => '44', 'format' => '10', 'flag' => '🇬🇧'),
        'FR' => array('name' => 'France', 'code' => '33', 'format' => '9', 'flag' => '🇫🇷'),
        'DE' => array('name' => 'Germany', 'code' => '49', 'format' => '10', 'flag' => '🇩🇪'),
        'IT' => array('name' => 'Italy', 'code' => '39', 'format' => '10', 'flag' => '🇮🇹'),
        'ES' => array('name' => 'Spain', 'code' => '34', 'format' => '9', 'flag' => '🇪🇸'),
        'NL' => array('name' => 'Netherlands', 'code' => '31', 'format' => '9', 'flag' => '🇳🇱'),
        'BE' => array('name' => 'Belgium', 'code' => '32', 'format' => '9', 'flag' => '🇧🇪'),
        'CH' => array('name' => 'Switzerland', 'code' => '41', 'format' => '9', 'flag' => '🇨🇭'),
        'AT' => array('name' => 'Austria', 'code' => '43', 'format' => '10', 'flag' => '🇦🇹'),
        'SE' => array('name' => 'Sweden', 'code' => '46', 'format' => '9', 'flag' => '🇸🇪'),
        'NO' => array('name' => 'Norway', 'code' => '47', 'format' => '8', 'flag' => '🇳🇴'),
        'DK' => array('name' => 'Denmark', 'code' => '45', 'format' => '8', 'flag' => '🇩🇰'),
        'PL' => array('name' => 'Poland', 'code' => '48', 'format' => '9', 'flag' => '🇵🇱'),
        'GR' => array('name' => 'Greece', 'code' => '30', 'format' => '10', 'flag' => '🇬🇷'),
        'PT' => array('name' => 'Portugal', 'code' => '351', 'format' => '9', 'flag' => '🇵🇹'),
        'TR' => array('name' => 'Turkey', 'code' => '90', 'format' => '10', 'flag' => '🇹🇷'),
        'RU' => array('name' => 'Russia', 'code' => '7', 'format' => '10', 'flag' => '🇷🇺'),
        
        // Americas
        'US' => array('name' => 'United States', 'code' => '1', 'format' => '10', 'flag' => '🇺🇸'),
        'CA' => array('name' => 'Canada', 'code' => '1', 'format' => '10', 'flag' => '🇨🇦'),
        'MX' => array('name' => 'Mexico', 'code' => '52', 'format' => '10', 'flag' => '🇲🇽'),
        'BR' => array('name' => 'Brazil', 'code' => '55', 'format' => '11', 'flag' => '🇧🇷'),
        'AR' => array('name' => 'Argentina', 'code' => '54', 'format' => '10', 'flag' => '🇦🇷'),
        'CL' => array('name' => 'Chile', 'code' => '56', 'format' => '9', 'flag' => '🇨🇱'),
        
        // Asia & Pacific
        'IN' => array('name' => 'India', 'code' => '91', 'format' => '10', 'flag' => '🇮🇳'),
        'PK' => array('name' => 'Pakistan', 'code' => '92', 'format' => '10', 'flag' => '🇵🇰'),
        'BD' => array('name' => 'Bangladesh', 'code' => '880', 'format' => '10', 'flag' => '🇧🇩'),
        'CN' => array('name' => 'China', 'code' => '86', 'format' => '11', 'flag' => '🇨🇳'),
        'JP' => array('name' => 'Japan', 'code' => '81', 'format' => '10', 'flag' => '🇯🇵'),
        'KR' => array('name' => 'South Korea', 'code' => '82', 'format' => '10', 'flag' => '🇰🇷'),
        'MY' => array('name' => 'Malaysia', 'code' => '60', 'format' => '9', 'flag' => '🇲🇾'),
        'SG' => array('name' => 'Singapore', 'code' => '65', 'format' => '8', 'flag' => '🇸🇬'),
        'TH' => array('name' => 'Thailand', 'code' => '66', 'format' => '9', 'flag' => '🇹🇭'),
        'ID' => array('name' => 'Indonesia', 'code' => '62', 'format' => '10', 'flag' => '🇮🇩'),
        'PH' => array('name' => 'Philippines', 'code' => '63', 'format' => '10', 'flag' => '🇵🇭'),
        'VN' => array('name' => 'Vietnam', 'code' => '84', 'format' => '9', 'flag' => '🇻🇳'),
        'AU' => array('name' => 'Australia', 'code' => '61', 'format' => '9', 'flag' => '🇦🇺'),
        'NZ' => array('name' => 'New Zealand', 'code' => '64', 'format' => '9', 'flag' => '🇳🇿'),
        
        // Africa
        'ZA' => array('name' => 'South Africa', 'code' => '27', 'format' => '9', 'flag' => '🇿🇦'),
        'NG' => array('name' => 'Nigeria', 'code' => '234', 'format' => '10', 'flag' => '🇳🇬'),
        'KE' => array('name' => 'Kenya', 'code' => '254', 'format' => '9', 'flag' => '🇰🇪'),
        'GH' => array('name' => 'Ghana', 'code' => '233', 'format' => '9', 'flag' => '🇬🇭'),
        'ET' => array('name' => 'Ethiopia', 'code' => '251', 'format' => '9', 'flag' => '🇪🇹'),
    );
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get all supported countries
     */
    public static function get_countries() {
        return self::$countries;
    }
    
    /**
     * Get country data by code
     */
    public static function get_country($country_code) {
        return isset(self::$countries[$country_code]) ? self::$countries[$country_code] : null;
    }
    
    /**
     * Format phone number with country code
     * 
     * @param string $phone Raw phone number
     * @param string $country_code Country code (e.g., 'EG', 'SA')
     * @return string|false Formatted phone with + or false on failure
     */
    public static function format($phone, $country_code = 'EG') {
        // Clean phone number - keep only digits
        $phone = preg_replace('/[^0-9]/', '', trim($phone));
        
        if (empty($phone)) {
            return false;
        }
        
        $country = self::get_country($country_code);
        if (!$country) {
            return false;
        }
        
        $dial_code = $country['code'];
        $expected_length = (int) $country['format'];
        
        // Remove leading zeros
        $phone = ltrim($phone, '0');
        
        // If already has country code, validate and return
        if (substr($phone, 0, strlen($dial_code)) === $dial_code) {
            $local = substr($phone, strlen($dial_code));
            if (strlen($local) === $expected_length) {
                return '+' . $phone;
            }
        }
        
        // Add country code if valid length
        if (strlen($phone) === $expected_length) {
            return '+' . $dial_code . $phone;
        }
        
        // Invalid format
        return false;
    }
    
    /**
     * Generate phone variants for database search
     * 
     * @param string $phone Phone number
     * @param string $country_code Country code
     * @return array Array of possible phone formats
     */
    public static function get_variants($phone, $country_code = 'EG') {
        $formatted = self::format($phone, $country_code);
        
        if (!$formatted) {
            return array();
        }
        
        $variants = array();
        $country = self::get_country($country_code);
        $dial_code = $country['code'];
        
        // Remove + for processing
        $clean = ltrim($formatted, '+');
        
        // Get local part
        $local = substr($clean, strlen($dial_code));
        
        // Generate all possible variants
        $variants[] = $formatted;                    // +20XXXXXXXXXX
        $variants[] = $clean;                        // 20XXXXXXXXXX
        $variants[] = '0' . $local;                  // 0XXXXXXXXXX
        $variants[] = $local;                        // XXXXXXXXXX
        
        return array_unique($variants);
    }
    
    /**
     * Validate phone number
     */
    public static function validate($phone, $country_code = 'EG') {
        return self::format($phone, $country_code) !== false;
    }
    
    /**
     * Get country code from phone number (smart detection)
     */
    public static function detect_country($phone) {
        $phone = preg_replace('/[^0-9]/', '', trim($phone));
        $phone = ltrim($phone, '+');
        
        // Sort countries by dial code length (longest first for accurate matching)
        $sorted_countries = self::$countries;
        uasort($sorted_countries, function($a, $b) {
            return strlen($b['code']) - strlen($a['code']);
        });
        
        // Try to match country codes by prefix
        foreach ($sorted_countries as $code => $data) {
            if (substr($phone, 0, strlen($data['code'])) === $data['code']) {
                return $code;
            }
        }
        
        // Default to Egypt
        return 'EG';
    }
    
    /**
     * Get countries for dropdown (sorted alphabetically)
     */
    public static function get_countries_for_dropdown() {
        $countries = self::$countries;
        
        // Sort alphabetically by name
        uasort($countries, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $countries;
    }
    
    /**
     * Get countries grouped by region (for better UX)
     */
    public static function get_countries_by_region() {
        return array(
            'Arab Countries' => array('EG', 'SA', 'AE', 'KW', 'QA', 'OM', 'BH', 'JO', 'LB', 'IQ', 'PS', 'SY', 'LY', 'TN', 'DZ', 'MA', 'SD', 'YE'),
            'Europe' => array('GB', 'FR', 'DE', 'IT', 'ES', 'NL', 'BE', 'CH', 'AT', 'SE', 'NO', 'DK', 'PL', 'GR', 'PT', 'TR', 'RU'),
            'Americas' => array('US', 'CA', 'MX', 'BR', 'AR', 'CL'),
            'Asia & Pacific' => array('IN', 'PK', 'BD', 'CN', 'JP', 'KR', 'MY', 'SG', 'TH', 'ID', 'PH', 'VN', 'AU', 'NZ'),
            'Africa' => array('ZA', 'NG', 'KE', 'GH', 'ET'),
        );
    }
}
