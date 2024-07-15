<?php
/*
Plugin Name: WP React Chat Plugin
Description: A simple chat feature for logged-in WordPress users to chat with juchheim.
Version: 1.0.0
Author: Ernest Juchheim
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include Composer autoload
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// Include the main class file.
require_once plugin_dir_path(__FILE__) . 'includes/class-wp-react-chat-plugin.php';

// Initialize the plugin.
add_action('plugins_loaded', array('WP_React_Chat_Plugin', 'init'));
