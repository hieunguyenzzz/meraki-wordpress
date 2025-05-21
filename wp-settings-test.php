<?php
// Load WordPress core
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define absolute path to WordPress directory
if (!defined('ABSPATH')) {
    define('ABSPATH', '/usr/src/wordpress/');
}

echo "Testing WordPress connection...<br>";

// Load wp-config.php
require_once(dirname(__FILE__) . '/wp-config.php');

// This will print errors if any
echo "WordPress configuration loaded.<br>";

try {
    // Try to load wp-settings.php
    echo "About to load WordPress settings...<br>";
    require_once(ABSPATH . 'wp-settings.php');
    echo "WordPress settings loaded successfully.<br>";
} catch (Exception $e) {
    echo "Error loading WordPress settings: " . $e->getMessage() . "<br>";
}
?> 