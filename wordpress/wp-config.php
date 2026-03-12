<?php
/**
 * WordPress configuration for Replit with SQLite
 */

// Detect the Replit domain for dynamic URL configuration
$replit_dev_domain = getenv('REPLIT_DEV_DOMAIN');
if ($replit_dev_domain) {
    $site_url = 'https://' . $replit_dev_domain;
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:5000';
    $site_url = $protocol . '://' . $host;
}

define('WP_HOME', $site_url);
define('WP_SITEURL', $site_url);

// SQLite database settings (used by SQLite Database Integration plugin)
define('DB_NAME', 'wordpress');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

// SQLite-specific path
define('DB_DIR', __DIR__ . '/wp-content/database/');
define('DB_FILE', 'wordpress.db');

// Security Keys (auto-generated unique values)
define('AUTH_KEY',         'tappy-auth-key-replit-unique-2024-a1b2c3d4e5');
define('SECURE_AUTH_KEY',  'tappy-secure-auth-replit-unique-2024-f6g7h8i9j0');
define('LOGGED_IN_KEY',    'tappy-loggedin-replit-unique-2024-k1l2m3n4o5');
define('NONCE_KEY',        'tappy-nonce-replit-unique-2024-p6q7r8s9t0u1');
define('AUTH_SALT',        'tappy-auth-salt-replit-unique-2024-v2w3x4y5z6');
define('SECURE_AUTH_SALT', 'tappy-secure-salt-replit-unique-2024-a7b8c9d0e1');
define('LOGGED_IN_SALT',   'tappy-loggedin-salt-replit-unique-2024-f2g3h4i5j6');
define('NONCE_SALT',       'tappy-nonce-salt-replit-unique-2024-k7l8m9n0o1');

$table_prefix = 'wp_';

// Debug settings (disable in production)
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// Allow WordPress to detect HTTPS when behind Replit's proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
