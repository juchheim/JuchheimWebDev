<?php
/**
 * Plugin Name: WP REST Chat Plugin
 * Description: A simple chat plugin using the WordPress REST API.
 * Version: 1.0.0
 * Author: Your Name
 */

// Register REST API routes
add_action('rest_api_init', function () {
    register_rest_route('chat/v1', '/messages', array(
        'methods' => 'GET',
        'callback' => 'get_chat_messages',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route('chat/v1', '/messages', array(
        'methods' => 'POST',
        'callback' => 'send_chat_message',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));
});

function get_chat_messages() {
    if (!is_user_logged_in()) {
        return new WP_Error('rest_forbidden', esc_html__('You cannot view the messages.'), array('status' => 401));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'chat_messages';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY timestamp ASC");

    if ($wpdb->last_error) {
        error_log('WordPress database error: ' . $wpdb->last_error);
        return new WP_Error('database_error', 'There was a problem retrieving messages from the database', array('status' => 500));
    }

    return $results;
}

function send_chat_message(WP_REST_Request $request) {
    if (!is_user_logged_in()) {
        return new WP_Error('rest_forbidden', esc_html__('You cannot send messages.'), array('status' => 401));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'chat_messages';

    $data = json_decode($request->get_body(), true);
    $content = sanitize_text_field($data['content']);
    $user = sanitize_text_field($data['user']);

    $wpdb->insert($table_name, array(
        'content' => $content,
        'user' => $user,
        'timestamp' => current_time('mysql'),
    ));

    if ($wpdb->last_error) {
        error_log('WordPress database error: ' . $wpdb->last_error);
        return new WP_Error('database_error', 'There was a problem saving the message to the database', array('status' => 500));
    }

    $message_id = $wpdb->insert_id;

    return array(
        'id' => $message_id,
        'content' => $content,
        'user' => $user,
        'timestamp' => current_time('mysql'),
    );
}

// Enqueue the React app
add_action('wp_enqueue_scripts', function () {
    $react_build_path = plugin_dir_path(__FILE__) . 'react-chat-frontend/build/static/js/';
    $files = scandir($react_build_path);
    $main_js = '';

    foreach ($files as $file) {
        if (strpos($file, 'main.') === 0 && strpos($file, '.js') !== false) {
            $main_js = $file;
            break;
        }
    }

    if ($main_js) {
        error_log('Enqueuing script: ' . $main_js);
        wp_enqueue_script('wp-rest-chat-frontend', plugins_url('react-chat-frontend/build/static/js/' . $main_js, __FILE__), array(), '1.0.0', true);

        // Localize the script with the REST API URL, nonce, and user data
        wp_localize_script('wp-rest-chat-frontend', 'wpRestChat', array(
            'apiUrl' => esc_url_raw(rest_url('chat/v1/')),
            'nonce' => wp_create_nonce('wp_rest'),
            'user' => is_user_logged_in() ? wp_get_current_user()->user_login : null,
        ));
    } else {
        error_log('Error: main.js file not found.');
    }
});

// Shortcode to render the chat app
function wp_rest_chat_shortcode() {
    return '<div id="wp-rest-chat-app"></div>';
}
add_shortcode('wp_rest_chat', 'wp_rest_chat_shortcode');

// Create the chat messages table on plugin activation
register_activation_hook(__FILE__, function () {
    global $wpdb;
    $table_name = $wpdb->prefix . 'chat_messages';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        content text NOT NULL,
        user varchar(255) NOT NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});

// Ensure the chat messages table exists when the plugin is loaded
add_action('plugins_loaded', function () {
    global $wpdb;
    $table_name = $wpdb->prefix . 'chat_messages';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        error_log('Chat messages table does not exist, creating table.');
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            content text NOT NULL,
            user varchar(255) NOT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
});
