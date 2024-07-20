<?php
/**
 * Plugin Name: Minimal Role Check
 * Description: A minimal plugin to check user roles and capabilities.
 * Version: 1.0.0
 * Author: Ernest Juchheim
 */

// Function to print debug information directly on the page
function print_debug($message) {
    echo '<div style="border: 1px solid red; padding: 10px; margin: 10px;">' . $message . '</div>';
}

// Shortcode to display user role and capability checks
function minimal_role_check_shortcode() {
    $current_user = wp_get_current_user();
    $roles = $current_user->roles;
    $can_create_room = current_user_can('subscriber');
    $can_list_room = current_user_can('administrator');

    // Display roles and capabilities
    $output = '<div style="padding: 10px; border: 1px solid green; margin: 10px;">';
    $output .= '<h3>User Role and Capability Check</h3>';
    $output .= '<p>User ID: ' . $current_user->ID . '</p>';
    $output .= '<p>Username: ' . $current_user->user_login . '</p>';
    $output .= '<p>Roles: ' . implode(', ', $roles) . '</p>';
    $output .= '<p>Can create room (subscriber): ' . ($can_create_room ? 'Yes' : 'No') . '</p>';
    $output .= '<p>Can list room (administrator): ' . ($can_list_room ? 'Yes' : 'No') . '</p>';
    $output .= '</div>';

    return $output;
}
add_shortcode('minimal_role_check', 'minimal_role_check_shortcode');

// Hook to print debug information when the plugin is loaded
add_action('init', function() {
    print_debug('Minimal Role Check Plugin Loaded');
});
