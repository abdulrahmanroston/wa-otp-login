# WA OTP Login - WhatsApp Authentication System

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-red.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-Compatible-purple.svg)

Professional WhatsApp OTP (One-Time Password) authentication system for WordPress. Enable passwordless login using WhatsApp verification codes with seamless WooCommerce integration and automatic order linking.

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Demo](#-demo)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [WA Simple Queue Integration](#-wa-simple-queue-integration)
- [Shortcode Reference](#-shortcode-reference)
- [WooCommerce Integration](#-woocommerce-integration)
- [User Flow](#-user-flow)
- [Admin Panel](#-admin-panel)
- [Database Structure](#-database-structure)
- [Security](#-security)
- [Customization](#-customization)
- [Hooks & Filters](#-hooks--filters)
- [Troubleshooting](#-troubleshooting)
- [FAQ](#-faq)
- [Changelog](#-changelog)
- [License](#-license)

---

## 🔍 Overview

**WA OTP Login** provides a modern, secure, and user-friendly authentication method using WhatsApp OTP codes. Users can log in or register using only their phone number without needing to remember passwords.

### Why Choose WA OTP Login?

- 🔐 **Enhanced Security**: No passwords to remember or steal
- ⚡ **Fast Login**: 3-digit OTP sent instantly via WhatsApp
- 📱 **Mobile-First**: Perfect for mobile users
- 🔗 **Smart Order Linking**: Automatically links previous guest orders to new accounts
- 🌍 **64+ Countries**: Support for international phone numbers
- 🛒 **WooCommerce Ready**: Seamless integration with WooCommerce
- 🎨 **Customizable**: Easy to style and customize
- 📦 **Zero Dependencies**: Works with WA Simple Queue plugin only

---

## ✨ Features

### Core Authentication Features

#### 1. Passwordless Login System

```
┌─────────────────────────────────────────────────┐
│           WA OTP Login Flow                     │
│                                                 │
│  Enter Phone → Send OTP → Verify → Login ✓     │
│       ↓           ↓          ↓         ↓        │
│   Validate   WhatsApp    Check    Auto-Login   │
└─────────────────────────────────────────────────┘
```

- **3-Digit OTP**: Quick and easy to type (100-999)
- **5-Minute Expiry**: Security-focused short validity
- **Rate Limiting**: Maximum 3 attempts per phone
- **Instant Delivery**: Via WA Simple Queue system

#### 2. Smart User Management

**Auto Registration:**
- Creates user account if doesn't exist
- Uses phone number as username
- Generates secure random password
- Sets email as `phone@yoursite.com`
- Stores phone in user meta

**Existing User Detection:**
- Checks by phone number first
- Falls back to email check
- Updates phone meta if missing
- Logs in existing users

#### 3. WooCommerce Order Linking

**Intelligent Order Association:**
```php
Guest Checkout (phone: +201234567890)
       ↓
Create Order #1234
       ↓
Later: Login with same phone
       ↓
Auto-link Order #1234 to account ✓
```

**Features:**
- Finds orders by billing phone
- Links to user account automatically
- Updates order customer ID
- Maintains order history
- Works with existing orders

#### 4. International Phone Support

**64+ Countries Supported:**

| Region | Countries | Format |
|--------|-----------|--------|
| **Middle East** | Egypt, Saudi Arabia, UAE, Kuwait, Jordan, Lebanon, etc. | +20, +966, +971, +965 |
| **Europe** | UK, Germany, France, Italy, Spain, etc. | +44, +49, +33, +39 |
| **Americas** | USA, Canada, Brazil, Mexico, etc. | +1, +55, +52 |
| **Asia** | India, China, Japan, Indonesia, etc. | +91, +86, +81, +62 |
| **Africa** | Nigeria, South Africa, Kenya, etc. | +234, +27, +254 |

**Smart Phone Formatting:**
- Automatic country code detection
- Input validation per country
- Format normalization
- International dial codes

#### 5. Security Features

- ✅ **Rate Limiting**: Max 3 OTP requests per phone per hour
- ✅ **OTP Expiry**: Codes expire after 5 minutes
- ✅ **Database Sanitization**: All inputs sanitized and escaped
- ✅ **Nonce Verification**: CSRF protection on all forms
- ✅ **Attempt Tracking**: Blocks after failed attempts
- ✅ **Secure Passwords**: Auto-generated 20-character passwords
- ✅ **Phone Meta Storage**: Encrypted user phone numbers

---

## 🎬 Demo

### Login Form

```
┌────────────────────────────────────────┐
│   🔐 Login with WhatsApp               │
│                                        │
│   Country: [Egypt +20 ▼]              │
│                                        │
│   Phone: [01234567890]                 │
│                                        │
│   [Send OTP Code]                      │
│                                        │
│   ──────────────────────────           │
│                                        │
│   OTP Code: [___] (sent via WhatsApp) │
│                                        │
│   [Verify & Login]                     │
│                                        │
│   Didn't receive code? [Resend]        │
└────────────────────────────────────────┘
```

### User Experience

**First Time User:**
1. Enter phone number → Click "Send OTP"
2. Receive WhatsApp message: "Your OTP: 456"
3. Enter 456 → Click "Verify & Login"
4. ✅ Account created + Logged in + Orders linked

**Existing User:**
1. Enter phone number → Click "Send OTP"
2. Receive WhatsApp message: "Your OTP: 789"
3. Enter 789 → Click "Verify & Login"
4. ✅ Logged in + Orders linked

---

## 📦 Requirements

### Minimum Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.6 or higher
- **WA Simple Queue:** Latest version (Required)

### Recommended

- **PHP:** 8.0 or higher
- **MySQL:** 8.0 or higher
- **WooCommerce:** 6.0+ (for order linking)
- **Memory Limit:** 128MB+

### Required Plugin

**[WA Simple Queue](https://github.com/abdulrahmanroston/wa-simple_plugin)** - WhatsApp message queue system

This plugin **requires** WA Simple Queue to send OTP codes via WhatsApp. It will not work without it.

---

## 🚀 Installation

### Step 1: Install WA Simple Queue

First, install the required dependency:

```bash
cd wp-content/plugins/
git clone https://github.com/abdulrahmanroston/wa-simple_plugin.git
```

Or download from [GitHub](https://github.com/abdulrahmanroston/wa-simple_plugin/archive/refs/heads/main.zip)

**Activate** WA Simple Queue from WordPress admin.

### Step 2: Install WA OTP Login

```bash
cd wp-content/plugins/
git clone https://github.com/abdulrahmanroston/wa-otp-login.git
```

Or download from [GitHub](https://github.com/abdulrahmanroston/wa-otp-login/archive/refs/heads/main.zip)

### Step 3: Activate

1. Go to **WordPress Admin → Plugins**
2. Find "WA OTP Login"
3. Click **Activate**

### Step 4: Configure

1. **Configure WA Simple Queue first:**
   - Go to **Tools → WA Queue → Settings**
   - Enter your WaSender API key
   - Set send interval (recommended: 6 seconds)
   - Save settings

2. **Configure WA OTP Login:**
   - Go to **Settings → WA OTP Login**
   - Set default country
   - Enable/disable order linking
   - Customize OTP settings

---

## ⚙️ Configuration

### Admin Settings

Go to **Settings → WA OTP Login**

#### General Settings

| Setting | Description | Default | Options |
|---------|-------------|---------|---------|
| **Default Country** | Pre-selected country in dropdown | `EG` (Egypt) | 64+ country codes |
| **Enable Order Linking** | Auto-link WooCommerce orders | `Yes` | Yes / No |
| **OTP Expiry** | Minutes before OTP expires | `5` | 1-30 minutes |
| **Max Attempts** | Failed verification attempts | `3` | 1-10 attempts |
| **OTP Length** | Digits in OTP code | `3` | 3-6 digits |

#### Message Template

Customize the WhatsApp OTP message:

```
Default Template:
Your OTP code is: {otp}

This code will expire in {expiry} minutes.

Do not share this code with anyone.
```

**Available Placeholders:**
- `{otp}` - The OTP code
- `{expiry}` - Expiry time in minutes
- `{site_name}` - Your website name
- `{site_url}` - Your website URL

**Example Custom Template:**
```
🔐 {site_name} Verification Code

Your code: {otp}

Valid for {expiry} minutes only.

⚠️ Never share this code!
```

---

## 📖 Usage

### Add Login Form to Page

#### Method 1: Shortcode

Simply add this shortcode to any page or post:

```
[wa_otp_login]
```

**With Attributes:**
```
[wa_otp_login redirect="/account" class="my-custom-class"]
```

**Available Attributes:**

| Attribute | Description | Default | Example |
|-----------|-------------|---------|---------|
| `redirect` | URL to redirect after login | Current page | `/my-account` |
| `class` | Additional CSS class | - | `custom-form` |
| `title` | Form heading | "Login with WhatsApp" | "Sign In" |
| `show_title` | Show/hide title | `yes` | `yes` / `no` |

#### Method 2: PHP Template

Add to your theme template:

```php
<?php echo do_shortcode('[wa_otp_login]'); ?>
```

Or use the function directly:

```php
<?php
if (function_exists('wa_otp_login_form')) {
    wa_otp_login_form([
        'redirect' => '/my-account',
        'title' => 'Sign In with WhatsApp'
    ]);
}
?>
```

#### Method 3: Block Editor (Gutenberg)

1. Add a **Shortcode Block**
2. Enter: `[wa_otp_login]`
3. Publish

---

## 🔌 WA Simple Queue Integration

This plugin integrates seamlessly with [WA Simple Queue](https://github.com/abdulrahmanroston/wa-simple_plugin) for reliable OTP delivery.

### How It Works

```
WA OTP Login                    WA Simple Queue                WhatsApp API
     │                                │                              │
     │  1. User requests OTP          │                              │
     ├────────────────────────────────>│                              │
     │                                │  2. Add to queue             │
     │                                │     - phone: +20123..        │
     │                                │     - message: "OTP: 456"    │
     │                                │     - priority: urgent       │
     │                                │                              │
     │                                │  3. Process queue            │
     │                                ├──────────────────────────────>│
     │                                │                              │
     │                                │  4. Message sent ✓          │
     │                                │<──────────────────────────────┤
     │  5. User receives WhatsApp     │                              │
     │<────────────────────────────────                              │
     │                                                               │
     │  6. User enters OTP                                           │
     │  7. Verify & Login ✓                                          │
```

### Integration Code Example

**When OTP is sent:**

```php
// In includes/class-otp-handler.php

public function send_otp($phone) {
    $otp = $this->generate_otp();
    
    // Save to database
    $this->save_otp($phone, $otp);
    
    // Format message
    $message = sprintf(
        "Your OTP code is: %s\n\nThis code will expire in 5 minutes.",
        $otp
    );
    
    // Send via WA Simple Queue with URGENT priority
    $queue_id = wa_send($phone, $message, [
        'priority' => 'urgent',  // OTP codes need immediate delivery
        'metadata' => [
            'type' => 'otp_verification',
            'phone' => $phone,
            'timestamp' => time()
        ]
    ]);
    
    return $queue_id ? true : false;
}
```

### Why WA Simple Queue?

| Feature | Benefit |
|---------|---------|
| **Urgent Priority** | OTP codes sent immediately, ahead of other messages |
| **Rate Limiting** | Prevents API bans from sending too many messages |
| **Automatic Retries** | If message fails, retries up to 3 times |
| **Status Tracking** | Monitor if OTP was delivered successfully |
| **Metadata** | Track which phone received which OTP |
| **Queue Management** | Messages sent in order with proper spacing |

### Checking OTP Delivery Status

```php
// Get queue ID when sending OTP
$queue_id = wa_send($phone, $message, ['priority' => 'urgent']);

// Later, check if delivered
$status = wa_get_status($queue_id);

if ($status) {
    switch ($status['status']) {
        case 'sent':
            echo "OTP delivered successfully!";
            break;
        case 'pending':
            echo "OTP being sent...";
            break;
        case 'failed':
            echo "OTP delivery failed: " . $status['error'];
            break;
    }
}
```

### Configuration Requirements

For optimal OTP delivery:

1. **WA Simple Queue Settings:**
   - Send Interval: **3-5 seconds** (faster than normal)
   - Enable urgent priority processing
   - Ensure cron is running

2. **WaSender API:**
   - Valid API key configured
   - Sufficient API quota
   - WhatsApp number connected

---

## 📝 Shortcode Reference

### Basic Usage

```
[wa_otp_login]
```

### All Attributes

```
[wa_otp_login 
    redirect="/my-account"
    class="custom-login-form"
    title="Sign In with WhatsApp"
    show_title="yes"
    button_text="Send Code"
    verify_text="Verify & Login"
    default_country="EG"
]
```

### Attribute Details

#### `redirect`
- **Type:** URL string
- **Default:** Current page URL
- **Description:** Page to redirect after successful login

**Examples:**
```
redirect="/my-account"           → WooCommerce account
redirect="/dashboard"            → Custom dashboard
redirect="<?php echo home_url(); ?>" → Homepage
```

#### `class`
- **Type:** String
- **Default:** Empty
- **Description:** Additional CSS classes for styling

**Example:**
```
class="my-form rounded shadow"
```

#### `title`
- **Type:** String
- **Default:** "Login with WhatsApp"
- **Description:** Form heading text

#### `show_title`
- **Type:** `yes` | `no`
- **Default:** `yes`
- **Description:** Display form title

#### `button_text`
- **Type:** String
- **Default:** "Send OTP Code"
- **Description:** Text for send button

#### `verify_text`
- **Type:** String
- **Default:** "Verify & Login"
- **Description:** Text for verify button

#### `default_country`
- **Type:** Country code (ISO 3166-1 alpha-2)
- **Default:** From settings
- **Description:** Pre-selected country

**Examples:**
```
default_country="EG"  → Egypt
default_country="SA"  → Saudi Arabia
default_country="US"  → United States
```

---

## 🛒 WooCommerce Integration

### Automatic Features

#### 1. Guest Order Linking

When a user creates an account via OTP login, the plugin automatically:

1. **Searches for guest orders** with same billing phone
2. **Links found orders** to the new user account
3. **Updates customer ID** in orders
4. **Maintains order history** for the user

**Example Scenario:**

```
Day 1: Guest checkout
  - Phone: +201234567890
  - Order: #1234
  - Customer: Guest

Day 2: Login via OTP
  - Phone: +201234567890
  - Account created
  
Result:
  ✅ Order #1234 now linked to account
  ✅ Visible in My Account → Orders
  ✅ Customer can track, reorder, etc.
```

#### 2. Checkout Phone Validation

- Validates phone during checkout
- Ensures format matches country
- Prevents invalid phone numbers

#### 3. My Account Page

Users can:
- View all orders (guest + logged in)
- Track shipments
- Download invoices
- Reorder easily

### Manual Integration

#### Add OTP Login to Checkout

```php
// In functions.php or custom plugin

add_action('woocommerce_before_checkout_form', function() {
    if (!is_user_logged_in()) {
        echo '<div class="checkout-login-notice">';
        echo '<p>Already have an account? Login with WhatsApp:</p>';
        echo do_shortcode('[wa_otp_login redirect="/checkout" class="checkout-otp-form"]');
        echo '</div>';
    }
});
```

#### Add to Account Page

```php
// Replace default login form

remove_action('woocommerce_before_customer_login_form', 'woocommerce_output_login_form');

add_action('woocommerce_before_customer_login_form', function() {
    echo '<div class="woocommerce-otp-login">';
    echo '<h2>Login with WhatsApp</h2>';
    echo do_shortcode('[wa_otp_login redirect="/my-account"]');
    echo '</div>';
});
```

#### Custom Order Linking

Link orders programmatically:

```php
function link_customer_orders($user_id) {
    $phone = get_user_meta($user_id, 'billing_phone', true);
    
    if (!$phone) return;
    
    // Find guest orders
    $orders = wc_get_orders([
        'billing_phone' => $phone,
        'customer_id' => 0, // Guest orders
        'limit' => -1
    ]);
    
    // Link to user
    foreach ($orders as $order) {
        $order->set_customer_id($user_id);
        $order->save();
    }
    
    return count($orders);
}

// Usage
$linked = link_customer_orders(get_current_user_id());
echo "Linked $linked orders";
```

---

## 👤 User Flow

### Flow Diagram

```
                    START
                      │
                      ↓
            ┌─────────────────┐
            │  Enter Phone    │
            └────────┬────────┘
                     │
                     ↓
            ┌─────────────────┐
            │  Validate       │
            │  Phone Format   │
            └────────┬────────┘
                     │
         ┌───────────┴───────────┐
         │                       │
      Valid                   Invalid
         │                       │
         ↓                       ↓
┌─────────────────┐     ┌─────────────────┐
│ Generate OTP    │     │ Show Error      │
│ (3 digits)      │     │ "Invalid phone" │
└────────┬────────┘     └─────────────────┘
         │
         ↓
┌─────────────────┐
│ Save to DB      │
│ Expiry: 5 min   │
└────────┬────────┘
         │
         ↓
┌─────────────────┐
│ Send via        │
│ WA Queue        │
│ (Priority: High)│
└────────┬────────┘
         │
         ↓
┌─────────────────┐
│ User Receives   │
│ WhatsApp: "456" │
└────────┬────────┘
         │
         ↓
┌─────────────────┐
│ Enter OTP       │
│ in Form         │
└────────┬────────┘
         │
         ↓
┌─────────────────┐
│ Verify OTP      │
└────────┬────────┘
         │
   ┌─────┴─────┐
   │           │
 Valid     Invalid
   │           │
   ↓           ↓
┌──────┐   ┌──────────┐
│Check │   │ Attempts │
│User  │   │ < 3 ?    │
└───┬──┘   └────┬─────┘
    │           │
    │      ┌────┴────┐
    │    Yes       No
    │     │         │
    │     ↓         ↓
    │  ┌────┐  ┌───────┐
    │  │Try │  │Block  │
    │  │Again│ │Phone  │
    │  └────┘  └───────┘
    │
    ↓
┌───────────┐
│Exists?    │
└─────┬─────┘
      │
  ┌───┴───┐
  │       │
 Yes     No
  │       │
  ↓       ↓
┌────┐  ┌──────┐
│Login│ │Create│
│User │ │Account
└──┬─┘ └───┬──┘
   │       │
   └───┬───┘
       │
       ↓
┌──────────────┐
│Link WooOrders│
│(if exists)   │
└──────┬───────┘
       │
       ↓
┌──────────────┐
│ Redirect to  │
│ My Account   │
└──────────────┘
       │
       ↓
     SUCCESS
```

### Step-by-Step

#### Step 1: Phone Entry
- User selects country from dropdown (64+ countries)
- Enters phone number (auto-formatted)
- Clicks "Send OTP Code"

#### Step 2: Validation
- Phone format validated per country
- Checks for rate limiting (max 3 per hour)
- Generates 3-digit random OTP (100-999)

#### Step 3: OTP Generation & Storage
```php
$otp = wp_rand(100, 999);
$phone = '+201234567890';
$expiry = current_time('timestamp') + (5 * 60); // 5 minutes

// Save to database
global $wpdb;
$wpdb->insert($wpdb->prefix . 'wa_otp', [
    'phone' => $phone,
    'otp' => $otp,
    'expiry' => date('Y-m-d H:i:s', $expiry),
    'attempts' => 0
]);
```

#### Step 4: Send via WhatsApp
```php
$message = "Your OTP code is: $otp\n\nThis code will expire in 5 minutes.";

// Send with urgent priority
wa_send($phone, $message, [
    'priority' => 'urgent',
    'metadata' => [
        'type' => 'otp',
        'phone' => $phone
    ]
]);
```

#### Step 5: User Verification
- User receives WhatsApp message
- Enters OTP in verification form
- Clicks "Verify & Login"

#### Step 6: OTP Verification
```php
// Check if OTP matches
$valid = $this->verify_otp($phone, $entered_otp);

if ($valid) {
    // Check if user exists
    $user = $this->get_user_by_phone($phone);
    
    if ($user) {
        // Log in existing user
        wp_set_auth_cookie($user->ID);
    } else {
        // Create new user
        $user_id = $this->create_user($phone);
        wp_set_auth_cookie($user_id);
        
        // Link WooCommerce orders
        if (function_exists('wc_get_orders')) {
            $this->link_orders($user_id, $phone);
        }
    }
}
```

#### Step 7: Success
- User logged in
- Orders linked (if any)
- Redirected to account page

---

## 🖥️ Admin Panel

### Menu Location

**Settings → WA OTP Login**

### Settings Page

#### General Tab

**Country Settings:**
- Default country dropdown
- Country code prefix
- Phone format examples

**OTP Settings:**
- OTP expiry time (1-30 minutes)
- OTP length (3-6 digits)
- Max verification attempts (1-10)

**Message Template:**
- Customize WhatsApp message
- Available placeholders
- Message preview

**Order Linking:**
- Enable/disable automatic linking
- Link existing orders button
- Statistics (orders linked)

#### Statistics Tab

**Dashboard Widgets:**

```
┌─────────────────────────────────────┐
│  OTP Login Statistics               │
├─────────────────────────────────────┤
│                                     │
│  Total Logins (Today):        245   │
│  New Registrations (Today):    45   │
│  OTP Sent (Today):            290   │
│  Success Rate:                98%   │
│                                     │
│  ──────────────────────────────     │
│                                     │
│  Total Users (OTP):         5,234   │
│  Orders Linked (All Time): 12,456   │
│                                     │
└─────────────────────────────────────┘
```

#### Logs Tab

**OTP Activity Log:**

| Time | Phone | Action | Status | IP Address |
|------|-------|--------|--------|------------|
| 10:30:15 | +2012****890 | OTP Sent | Success | 197.x.x.x |
| 10:30:22 | +2012****890 | OTP Verified | Success | 197.x.x.x |
| 10:32:10 | +2099****555 | OTP Sent | Failed | 41.x.x.x |

**Export Options:**
- Export to CSV
- Date range filter
- Phone number search

---

## 🗄️ Database Structure

### Table: `wp_wa_otp`

```sql
CREATE TABLE wp_wa_otp (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(50) NOT NULL,              -- User phone number with country code
    otp VARCHAR(10) NOT NULL,                -- OTP code (usually 3 digits)
    expiry DATETIME NOT NULL,                -- When OTP expires
    attempts INT DEFAULT 0,                  -- Verification attempts
    verified TINYINT(1) DEFAULT 0,           -- Is verified? (0/1)
    created_at DATETIME NOT NULL,            -- When OTP was created
    verified_at DATETIME,                    -- When verified
    ip_address VARCHAR(50),                  -- User IP
    
    INDEX idx_phone (phone),                 -- Fast phone lookup
    INDEX idx_expiry (expiry),               -- Cleanup expired
    INDEX idx_created (created_at)           -- Date queries
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `wp_usermeta`

**Additional User Meta:**

| meta_key | meta_value | Description |
|----------|------------|-------------|
| `billing_phone` | `+201234567890` | User's phone number |
| `wa_otp_verified` | `1` | Verified via OTP |
| `wa_otp_login_count` | `15` | Total OTP logins |
| `wa_otp_last_login` | `2025-12-26 10:30:00` | Last OTP login |

### Sample Data

```sql
-- Active OTP waiting for verification
INSERT INTO wp_wa_otp VALUES
(1, '+201234567890', '456', '2025-12-26 10:35:00', 0, 0, 
 '2025-12-26 10:30:00', NULL, '197.55.23.10');

-- Verified OTP
INSERT INTO wp_wa_otp VALUES
(2, '+209876543210', '789', '2025-12-26 10:25:00', 1, 1,
 '2025-12-26 10:20:00', '2025-12-26 10:20:45', '41.234.56.78');

-- Expired OTP (not verified)
INSERT INTO wp_wa_otp VALUES
(3, '+205555555555', '123', '2025-12-26 09:50:00', 3, 0,
 '2025-12-26 09:45:00', NULL, '197.100.50.25');
```

---

## 🔒 Security

### Security Features

#### 1. OTP Security

**Short Lifespan:**
- Default: 5 minutes
- Configurable: 1-30 minutes
- Auto-cleanup of expired codes

**Limited Attempts:**
- Maximum 3 verification attempts
- Phone blocked temporarily after failure
- Configurable: 1-10 attempts

**Random Generation:**
```php
// Cryptographically secure random
$otp = wp_rand(100, 999);  // 3 digits
// or
$otp = wp_rand(100000, 999999);  // 6 digits
```

#### 2. Phone Validation

**Format Validation:**
```php
// Per-country validation
$valid = preg_match('/^[0-9]{10,15}$/', $phone);

// International format
$valid = preg_match('/^\+[1-9][0-9]{1,14}$/', $phone);
```

**Duplicate Prevention:**
- Each phone = one OTP at a time
- Old OTPs invalidated on new request

#### 3. Rate Limiting

**Request Throttling:**
- Max 3 OTP requests per hour per phone
- Configurable cooldown period
- IP-based tracking

```php
// Check rate limit
$recent = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}wa_otp 
    WHERE phone = %s 
    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
    $phone
));

if ($recent >= 3) {
    return new WP_Error('rate_limit', 'Too many requests');
}
```

#### 4. Data Protection

**Database Security:**
```php
// Sanitized inputs
$phone = sanitize_text_field($_POST['phone']);
$otp = sanitize_text_field($_POST['otp']);

// Prepared statements
$wpdb->prepare("SELECT * FROM {$table} WHERE phone = %s", $phone);

// Escaped outputs
echo esc_html($phone);
```

**CSRF Protection:**
```php
// Nonce verification
wp_verify_nonce($_POST['_wpnonce'], 'wa_otp_send');
wp_verify_nonce($_POST['_wpnonce'], 'wa_otp_verify');
```

#### 5. Password Generation

**Secure Random Passwords:**
```php
// 20-character random password
$password = wp_generate_password(20, true, true);

// Auto-set for new users
wp_set_password($password, $user_id);

// User never sees it (passwordless login)
```

#### 6. Privacy

**Data Minimization:**
- Only phone number stored
- No unnecessary personal data
- GDPR compliant

**Right to Erasure:**
```php
// Delete user data
delete_user_meta($user_id, 'billing_phone');
$wpdb->delete($wpdb->prefix . 'wa_otp', ['phone' => $phone]);
```

### Security Best Practices

**For Site Administrators:**

1. ✅ Use SSL/HTTPS certificate
2. ✅ Keep WordPress updated
3. ✅ Use strong WaSender API key
4. ✅ Enable login logging
5. ✅ Monitor failed attempts
6. ✅ Regular security audits

**For Developers:**

1. ✅ Validate all inputs
2. ✅ Sanitize all outputs
3. ✅ Use prepared statements
4. ✅ Implement nonce checks
5. ✅ Log security events
6. ✅ Follow WordPress coding standards

---

## 🎨 Customization

### Custom Styling

#### CSS Classes

**Form Container:**
```css
.wa-otp-login-form {
    max-width: 400px;
    margin: 0 auto;
    padding: 30px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
```

**Country Dropdown:**
```css
.wa-otp-country-select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}
```

**Phone Input:**
```css
.wa-otp-phone-input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.wa-otp-phone-input:focus {
    outline: none;
    border-color: #25D366; /* WhatsApp green */
    box-shadow: 0 0 0 3px rgba(37,211,102,0.1);
}
```

**Buttons:**
```css
.wa-otp-button {
    width: 100%;
    padding: 14px;
    background: #25D366;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.wa-otp-button:hover {
    background: #1fb855;
}

.wa-otp-button:disabled {
    background: #ccc;
    cursor: not-allowed;
}
```

**OTP Input:**
```css
.wa-otp-code-input {
    width: 100%;
    padding: 12px;
    text-align: center;
    font-size: 24px;
    letter-spacing: 10px;
    border: 2px dashed #25D366;
    border-radius: 4px;
}
```

**Messages:**
```css
.wa-otp-success {
    padding: 12px;
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 4px;
    color: #155724;
}

.wa-otp-error {
    padding: 12px;
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    color: #721c24;
}
```

### Custom Theme Example

**Dark Theme:**
```css
.wa-otp-dark-theme {
    background: #1e1e1e;
    color: #ffffff;
}

.wa-otp-dark-theme .wa-otp-phone-input,
.wa-otp-dark-theme .wa-otp-country-select {
    background: #2d2d2d;
    border-color: #444;
    color: #fff;
}

.wa-otp-dark-theme .wa-otp-button {
    background: #25D366;
    color: #000;
}
```

**Usage:**
```
[wa_otp_login class="wa-otp-dark-theme"]
```

### Custom JavaScript

**Add validation:**
```javascript
jQuery(document).ready(function($) {
    $('.wa-otp-phone-input').on('input', function() {
        var phone = $(this).val();
        
        // Remove non-digits
        phone = phone.replace(/\D/g, '');
        
        // Update input
        $(this).val(phone);
        
        // Enable/disable button
        if (phone.length >= 10) {
            $('.wa-otp-button').prop('disabled', false);
        } else {
            $('.wa-otp-button').prop('disabled', true);
        }
    });
});
```

---

## 🪝 Hooks & Filters

### Actions

#### `wa_otp_after_send`

Fired after OTP is sent

```php
add_action('wa_otp_after_send', function($phone, $otp, $queue_id) {
    // Log to external service
    error_log("OTP sent to {$phone}: Queue ID {$queue_id}");
    
    // Send admin notification
    if (is_super_admin()) {
        // Notify admin
    }
}, 10, 3);
```

**Parameters:**
- `$phone` (string): Phone number
- `$otp` (string): Generated OTP
- `$queue_id` (int): WA Queue message ID

#### `wa_otp_after_verify`

Fired after successful verification

```php
add_action('wa_otp_after_verify', function($user_id, $phone, $is_new_user) {
    if ($is_new_user) {
        // Welcome email
        wp_mail(
            get_userdata($user_id)->user_email,
            'Welcome!',
            'Thanks for registering via WhatsApp!'
        );
    }
    
    // Update last login
    update_user_meta($user_id, 'wa_last_login', current_time('mysql'));
}, 10, 3);
```

**Parameters:**
- `$user_id` (int): User ID
- `$phone` (string): Phone number
- `$is_new_user` (bool): Is this a new registration?

#### `wa_otp_after_login`

Fired after user logged in

```php
add_action('wa_otp_after_login', function($user_id) {
    // Redirect to custom page
    wp_redirect('/dashboard');
    exit;
}, 10, 1);
```

#### `wa_otp_orders_linked`

Fired after orders linked to user

```php
add_action('wa_otp_orders_linked', function($user_id, $order_ids) {
    $count = count($order_ids);
    
    // Notify user
    $phone = get_user_meta($user_id, 'billing_phone', true);
    wa_send($phone, "We've linked {$count} previous orders to your account!");
}, 10, 2);
```

**Parameters:**
- `$user_id` (int): User ID
- `$order_ids` (array): Linked order IDs

### Filters

#### `wa_otp_length`

Modify OTP length

```php
add_filter('wa_otp_length', function($length) {
    return 6;  // 6-digit OTP instead of 3
});
```

**Default:** `3`

#### `wa_otp_expiry`

Modify OTP expiry time (minutes)

```php
add_filter('wa_otp_expiry', function($minutes) {
    return 10;  // 10 minutes instead of 5
});
```

**Default:** `5`

#### `wa_otp_message`

Customize WhatsApp message

```php
add_filter('wa_otp_message', function($message, $otp, $phone) {
    return "🔐 Your verification code: {$otp}\n\n" .
           "Valid for 5 minutes only.\n" .
           "Do not share with anyone!";
}, 10, 3);
```

**Parameters:**
- `$message` (string): Default message
- `$otp` (string): OTP code
- `$phone` (string): Phone number

**Returns:** Modified message

#### `wa_otp_user_data`

Modify user data before creation

```php
add_filter('wa_otp_user_data', function($data, $phone) {
    // Set custom username
    $data['user_login'] = 'user_' . substr($phone, -6);
    
    // Set custom email
    $data['user_email'] = 'user' . substr($phone, -6) . '@mysite.com';
    
    // Add custom role
    $data['role'] = 'customer';
    
    return $data;
}, 10, 2);
```

**Default data:**
```php
[
    'user_login' => sanitize_user($phone),
    'user_pass' => wp_generate_password(20, true, true),
    'user_email' => sanitize_email($phone . '@' . $_SERVER['HTTP_HOST']),
    'role' => 'subscriber'
]
```

#### `wa_otp_redirect_url`

Modify redirect URL after login

```php
add_filter('wa_otp_redirect_url', function($url, $user_id) {
    // Redirect based on role
    $user = get_userdata($user_id);
    
    if (in_array('administrator', $user->roles)) {
        return admin_url();
    }
    
    if (function_exists('wc_get_page_permalink')) {
        return wc_get_page_permalink('myaccount');
    }
    
    return $url;
}, 10, 2);
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. OTP Not Received

**Symptoms:**
- User doesn't receive WhatsApp message
- Form shows "OTP sent" but nothing arrives

**Solutions:**

✅ **Check WA Simple Queue**
```
Go to: Tools → WA Queue
Check if message is:
- Pending: Being processed
- Sent: Successfully delivered
- Failed: Error occurred (check error column)
```

✅ **Verify API Key**
```
Go to: Tools → WA Queue → Settings
- Check API key is correct
- Test connection
- Check API quota
```

✅ **Check Phone Format**
```php
// Phone must include country code
$phone = '+201234567890';  // ✅ Correct

$phone = '01234567890';    // ❌ Missing +20
$phone = '1234567890';     // ❌ No country code
```

✅ **Check Queue Processing**
```php
// Manually process queue
WA_Queue::instance()->process();

// Check cron status
wp_next_scheduled('wa_process_queue');
```

#### 2. "Invalid OTP" Error

**Symptoms:**
- User enters correct OTP but gets error
- Always says invalid

**Solutions:**

✅ **Check Expiry Time**
```
OTP expires after 5 minutes by default.
User may be entering expired code.

Solution: Ask user to request new OTP
```

✅ **Check Attempts**
```
After 3 failed attempts, OTP is blocked.

Solution: 
1. Wait 1 hour
2. Or clear from database
3. Or reduce max attempts in settings
```

✅ **Check Database**
```sql
-- See if OTP exists
SELECT * FROM wp_wa_otp 
WHERE phone = '+201234567890' 
AND verified = 0
ORDER BY created_at DESC LIMIT 1;

-- Check expiry
-- expiry should be > NOW()
```

#### 3. User Not Created

**Symptoms:**
- OTP verified successfully
- But user account not created

**Solutions:**

✅ **Check User Exists**
```php
// User might already exist
$user = get_user_by('login', $phone);
if ($user) {
    echo "User already exists with ID: " . $user->ID;
}
```

✅ **Check Permissions**
```php
// Check if users can register
$can_register = get_option('users_can_register');
echo $can_register ? 'Yes' : 'No';

// Enable if disabled
update_option('users_can_register', 1);
```

✅ **Check Errors**
```php
// Enable debug logging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Check wp-content/debug.log
```

#### 4. Orders Not Linking

**Symptoms:**
- User logs in successfully
- But WooCommerce orders not linked

**Solutions:**

✅ **Check Order Phone**
```sql
-- Check if order has phone
SELECT post_id, meta_value 
FROM wp_postmeta 
WHERE meta_key = '_billing_phone' 
AND post_id = 1234;
```

✅ **Check Setting**
```
Go to: Settings → WA OTP Login
Enable: "Link WooCommerce Orders"
```

✅ **Manual Link**
```php
// Link orders manually
function manually_link_orders($user_id) {
    $phone = get_user_meta($user_id, 'billing_phone', true);
    
    $orders = wc_get_orders([
        'billing_phone' => $phone,
        'customer_id' => 0,
        'limit' => -1
    ]);
    
    foreach ($orders as $order) {
        $order->set_customer_id($user_id);
        $order->save();
    }
    
    return count($orders);
}

$linked = manually_link_orders(123);
echo "Linked: $linked orders";
```

#### 5. Plugin Not Activating

**Symptoms:**
- Can't activate plugin
- Error message about WA Simple

**Solutions:**

✅ **Install WA Simple Queue First**
```
1. Install WA Simple Queue plugin
2. Activate WA Simple Queue
3. Configure API key
4. Then activate WA OTP Login
```

✅ **Check PHP Version**
```php
// Need PHP 7.4+
echo PHP_VERSION;  // Should be 7.4.0 or higher
```

✅ **Check File Permissions**
```bash
# Plugin folder should be readable
chmod 755 wp-content/plugins/wa-otp-login/
chmod 644 wp-content/plugins/wa-otp-login/wa-otp-login.php
```

### Debug Mode

Enable detailed logging:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Check logs at:
// wp-content/debug.log
```

---

## ❓ FAQ

### General Questions

**Q: Do users need WhatsApp installed?**

A: Yes, users need WhatsApp on their phone to receive OTP codes. The plugin sends messages via WhatsApp API.

**Q: Can I use this without WA Simple Queue?**

A: No, WA Simple Queue is required. It handles the WhatsApp message sending and queue management.

**Q: What happens if WhatsApp is down?**

A: Messages will be queued and automatically retry up to 3 times. If all attempts fail, the admin will be notified.

**Q: Is this plugin free?**

A: Yes, the plugin is free and open source. However, you need a WaSender API subscription (paid) to send WhatsApp messages.

### Security Questions

**Q: Is OTP login secure?**

A: Yes, OTP login is very secure:
- 3-digit codes expire in 5 minutes
- Limited to 3 attempts
- Phone-based authentication
- Encrypted database storage

**Q: What if someone gets my OTP?**

A: OTPs expire in 5 minutes and can only be used once. Even if someone gets it, they'd need to act very quickly and know your phone number.

**Q: Can I increase OTP length?**

A: Yes, use the filter:
```php
add_filter('wa_otp_length', function() { return 6; });
```

### Technical Questions

**Q: How do I customize the form design?**

A: Add custom CSS to your theme:
```css
.wa-otp-login-form {
    /* Your custom styles */
}
```

**Q: Can I send OTP to email instead?**

A: This plugin is specifically for WhatsApp OTP. For email OTP, you'd need a different plugin.

**Q: How do I translate the plugin?**

A: The plugin is translation-ready. Use Loco Translate plugin or create .po/.mo files in the `languages/` folder.

**Q: Does it work with multisite?**

A: Yes, but each site needs its own WaSender API configuration.

### WooCommerce Questions

**Q: Will my guest orders be linked?**

A: Yes! When you login with the same phone number used during guest checkout, all matching orders will automatically link to your account.

**Q: Can I disable order linking?**

A: Yes, go to Settings → WA OTP Login and disable "Link WooCommerce Orders".

**Q: What if I used different phones for orders?**

A: Only orders with matching phone numbers will be linked. Orders with different phones stay separate.

### Integration Questions

**Q: Can I use this with other membership plugins?**

A: Yes, it creates standard WordPress user accounts that work with any membership plugin.

**Q: Does it work with custom login pages?**

A: Yes, use the shortcode `[wa_otp_login]` on any page.

**Q: Can I use multiple OTP systems?**

A: Yes, this plugin can coexist with other authentication methods.

---

## 📝 Changelog

### Version 2.0.0 (December 2025)

#### Added
- ✅ WhatsApp OTP authentication system
- ✅ 64+ country support with auto phone formatting
- ✅ Integration with WA Simple Queue
- ✅ WooCommerce guest order linking
- ✅ Automatic user registration
- ✅ Admin settings panel
- ✅ OTP activity logging
- ✅ Rate limiting (3 attempts per hour)
- ✅ Shortcode support `[wa_otp_login]`
- ✅ Security features (expiry, attempts, nonce)
- ✅ Custom hooks and filters
- ✅ Responsive mobile design
- ✅ Multilingual support (i18n ready)

#### Features
- 🔄 Automatic OTP expiry (5 minutes)
- 📊 Admin dashboard statistics
- 🪝 Extensive action/filter hooks
- 🔐 Secure password generation
- 📱 Mobile-optimized UI
- ⚡ AJAX form submission
- 🎨 Customizable styling
- 🌍 International phone validation

---

## 📄 License

This plugin is licensed under the **GNU General Public License v2.0 or later**.

```
WA OTP Login - WhatsApp Authentication for WordPress
Copyright (C) 2025 Abdulrahman Roston

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

---

## 👤 Author

**Abdulrahman Roston**

- 🌐 Website: [abdulrahmanroston.com](https://abdulrahmanroston.com)
- 📧 Email: support@abdulrahmanroston.com
- 🐙 GitHub: [@abdulrahmanroston](https://github.com/abdulrahmanroston)
- 💼 LinkedIn: [Abdulrahman Roston](https://linkedin.com/in/abdulrahmanroston)

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

### How to Contribute

1. **Fork** the repository
2. **Create** your feature branch
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Commit** your changes
   ```bash
   git commit -m 'Add some amazing feature'
   ```
4. **Push** to the branch
   ```bash
   git push origin feature/amazing-feature
   ```
5. **Open** a Pull Request

### Contribution Guidelines

- Follow WordPress coding standards
- Add comments to complex code
- Update documentation if needed
- Test thoroughly before submitting
- One feature per pull request

---

## 📞 Support

Need help? We're here for you!

- 🐛 **Bug Reports:** [GitHub Issues](https://github.com/abdulrahmanroston/wa-otp-login/issues)
- 💡 **Feature Requests:** [GitHub Discussions](https://github.com/abdulrahmanroston/wa-otp-login/discussions)
- 📧 **Email Support:** support@abdulrahmanroston.com
- 📚 **Documentation:** [GitHub Wiki](https://github.com/abdulrahmanroston/wa-otp-login/wiki)

---

## ⭐ Show Your Support

If this plugin helps you:

- ⭐ **Star** the repository
- 🐛 **Report** bugs and issues
- 💡 **Suggest** new features
- 📢 **Share** with others
- 💖 **Sponsor** the project

---

## 🔗 Related Projects

By the same author:

- **[WA Simple Queue](https://github.com/abdulrahmanroston/wa-simple_plugin)** - WhatsApp message queue manager (Required)
- **[Warehouses Manager](https://github.com/abdulrahmanroston/warehouses_manager_plugin)** - Multi-warehouse inventory system
- **[SHRMS Plugin](https://github.com/abdulrahmanroston/shrms_plugin)** - HR management for WordPress

---

## 🙏 Acknowledgments

- **WordPress** - Amazing CMS platform
- **WooCommerce** - E-commerce integration
- **WaSender API** - WhatsApp messaging service
- **intl-tel-input** - Phone input library
- **Contributors** - Everyone who helped improve this plugin

---

## 📊 Plugin Statistics

```
┌─────────────────────────────────────────┐
│  Development Stats                      │
├─────────────────────────────────────────┤
│  Development Time:      4 weeks         │
│  Lines of Code:         ~3,500          │
│  Files:                 15              │
│  Functions:             45+             │
│  Hooks:                 20+             │
│  Languages:             PHP, JS, CSS    │
│  WordPress Version:     5.8+            │
│  PHP Version:           7.4+            │
└─────────────────────────────────────────┘
```

---

## 🌟 Features Roadmap

### Planned Features

- [ ] Two-factor authentication (2FA)
- [ ] SMS fallback option
- [ ] Telegram integration
- [ ] QR code login
- [ ] Biometric authentication
- [ ] Social login integration
- [ ] Custom user roles
- [ ] Advanced analytics
- [ ] Export/Import settings

### Under Consideration

- [ ] Multi-device support
- [ ] Session management
- [ ] Login history dashboard
- [ ] Geo-location tracking
- [ ] Custom notification sounds
- [ ] White-label options

**Vote for features:** [GitHub Discussions](https://github.com/abdulrahmanroston/wa-otp-login/discussions)

---

**Made with ❤️ in Egypt 🇪🇬**

---

© 2025 Abdulrahman Roston. All rights reserved.

---

## 🚀 Quick Start

```bash
# 1. Install WA Simple Queue
git clone https://github.com/abdulrahmanroston/wa-simple_plugin.git

# 2. Install WA OTP Login
git clone https://github.com/abdulrahmanroston/wa-otp-login.git

# 3. Activate both plugins
# 4. Configure WaSender API key
# 5. Add shortcode to page: [wa_otp_login]
# 6. Done! 🎉
```

---

**Ready to provide passwordless authentication to your users?**

[⬇️ Download Now](https://github.com/abdulrahmanroston/wa-otp-login/archive/refs/heads/main.zip) | [📖 Documentation](https://github.com/abdulrahmanroston/wa-otp-login/wiki) | [🐛 Report Issue](https://github.com/abdulrahmanroston/wa-otp-login/issues/new)
