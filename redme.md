# WA OTP Login

Professional WhatsApp OTP login system for WordPress with WooCommerce integration.

## Features

- ✅ Login with WhatsApp OTP (3-digit code)
- ✅ Support for 64+ countries
- ✅ Automatic user creation
- ✅ Link previous orders to new accounts
- ✅ Clean and fast - no performance impact
- ✅ Fully integrated with WA Simple Queue
- ✅ Responsive design
- ✅ Easy to customize

## Requirements

- WordPress 5.8+
- PHP 7.4+
- **WA Simple Queue plugin** (required)
- WooCommerce (optional, for order linking)

## Installation

1. Upload the `wa-otp-login` folder to `/wp-content/plugins/`
2. Activate the plugin through WordPress admin
3. Make sure **WA Simple Queue** plugin is installed and configured
4. Go to **WA OTP Login > Settings** to configure
5. Use shortcode `[wa_otp_login]` on any page

## Shortcode Usage

### Basic usage:
[wa_otp_login]

text

### With custom redirect:
[wa_otp_login redirect="/my-account"]

text

### Without title:
[wa_otp_login show_title="no"]

text

### Custom title:
[wa_otp_login title="Sign in with WhatsApp"]

text

## File Structure

wa-otp-login/
├── wa-otp-login.php # Main plugin file
├── includes/
│ ├── class-phone-handler.php # Phone formatting & validation
│ ├── class-otp-handler.php # OTP generation & verification
│ ├── class-user-handler.php # User creation & order linking
│ ├── class-frontend.php # Shortcode & frontend display
│ └── class-admin.php # Admin settings
├── assets/
│ ├── css/
│ │ ├── frontend.css # Frontend styles
│ │ └── admin.css # Admin styles
│ └── js/
│ └── frontend.js # Frontend AJAX handler
└── README.md

text

## Customization

### Customize OTP Message

Edit the message template in `includes/class-otp-handler.php`:

private static function get_otp_message() {
return "Your verification code: {otp}

Valid for {expiry} minutes.";
}

text

### Change OTP Length

Currently fixed at 3 digits (100-999). To change, edit `generate_otp()` in `class-otp-handler.php`.

### Add More Countries

Edit the `$countries` array in `includes/class-phone-handler.php`.

## Support

For support, please contact: https://tenderfrozen.com

## Changelog

### 1.0.0
- Initial release
- WhatsApp OTP login
- 64+ countries support
- Order linking
- WA Simple Queue integration
📦 هيكل المجلدات الكامل:
text
wa-otp-login/
├── wa-otp-login.php
├── README.md
├── includes/
│   ├── class-phone-handler.php
│   ├── class-otp-handler.php
│   ├── class-user-handler.php
│   ├── class-frontend.php
│   └── class-admin.php
├── assets/
│   ├── css/
│   │   ├── frontend.css
│   │   └── admin.css
│   └── js/
│       └── frontend.js
└── languages/
    └── (empty for now)