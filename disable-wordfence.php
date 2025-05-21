<?php
// Don't output anything before WordPress loads
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define absolute path to WordPress directory
if (!defined('ABSPATH')) {
    define('ABSPATH', '/usr/src/wordpress/');
}

// Load WordPress
require_once(dirname(__FILE__) . '/wp-load.php');

// Disable Wordfence plugin
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (is_plugin_active('wordfence/wordfence.php')) {
    deactivate_plugins('wordfence/wordfence.php');
    echo "Wordfence deactivated successfully.";
} else {
    echo "Wordfence is not active.";
}
?> 