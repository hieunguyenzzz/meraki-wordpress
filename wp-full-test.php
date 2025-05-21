<?php
// Load WordPress core with full environment
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define absolute path to WordPress directory
if (!defined('ABSPATH')) {
    define('ABSPATH', '/usr/src/wordpress/');
}

echo "Testing WordPress environment...<br>";

// Load WordPress
require_once(dirname(__FILE__) . '/wp-load.php');

// Check database connection
echo "Database connection check: ";
if ($wpdb && $wpdb->db_connect()) {
    echo "Success<br>";
} else {
    echo "Failed<br>";
}

// Get WordPress version
echo "WordPress version: " . get_bloginfo('version') . "<br>";

// Show home URL
echo "Home URL: " . get_home_url() . "<br>";

// Try to disable all plugins
echo "Attempting to disable plugins...<br>";
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
deactivate_plugins('wordfence/wordfence.php');

echo "Test complete.<br>";
?> 