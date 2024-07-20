<?php
/**
 * Plugin Name: Debug REST Chat
 * Description: A simplified chat plugin for debugging roles and permissions.
 * Version: 1.0.0
 * Author: Ernest Juchheim
 */

// Hook to check user login and redirect if necessary
add_action('template_redirect', 'check_user_login_and_redirect');
function check_user_login_and_redirect() {
    if (is_page('chat') && !is_user_logged_in()) {
        // Redirect to login page
        $login_url = wp_login_url(home_url('/chat/'));
        wp_safe_redirect($login_url);
        exit();
    }
}

// Shortcode to display role-based content and generate nonce
function debug_rest_chat_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to access the chat.</p>';
    }

    $current_user = wp_get_current_user();
    $roles = $current_user->roles;

    // Check user capabilities
    $can_create_room = current_user_can('subscriber');
    $can_list_rooms = current_user_can('administrator');

    // Generate nonce for REST API
    $nonce = wp_create_nonce('wp_rest');

    // Display roles and capabilities with debug information
    $output = '<div id="rest-chat-wrapper">';

    if ($can_create_room) {
        $output .= '<div><button id="create-room-button">Create a Chat Room</button></div>';
    } elseif ($can_list_rooms) {
        $output .= '<div id="chat-rooms-list">Administrator: List chat rooms will be here.</div>';
    } else {
        $output .= '<p>Please log in to access the chat.</p>';
    }

    // Add hidden elements for managing chat rooms and messages
    $output .= '<div id="chat-room-container">';
    $output .= '<div id="chat-messages"></div>';
    $output .= '<input type="hidden" id="room-id">';
    $output .= '<input type="text" id="message-content" placeholder="Enter your message">';
    $output .= '<br><button id="post-message-button">Send Message</button>';
    $output .= '</div>';

    $output .= '</div>';
    return $output;
}
add_shortcode('debug_rest_chat', 'debug_rest_chat_shortcode');

// Enqueue and localize the script with nonce
function enqueue_rest_chat_scripts() {
    $script_url = plugins_url('rest-chat.js', __FILE__);
    wp_enqueue_script('rest-chat', $script_url, array('jquery'), null, true);

    // Localize script with nonce and API URL
    wp_localize_script('rest-chat', 'restChatSettings', array(
        'nonce' => wp_create_nonce('wp_rest'),
        'apiUrl' => rest_url('wp-rest-chat/v1/'),
        'currentUser' => wp_get_current_user()->user_login,
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_rest_chat_scripts');

// Register REST API routes
add_action('rest_api_init', function () {
    // Register route for creating a chat room
    register_rest_route('wp-rest-chat/v1', '/rooms', array(
        'methods' => 'POST',
        'callback' => 'wp_rest_chat_create_room',
        'permission_callback' => function ($request) {
            $nonce = $request->get_header('X-WP-Nonce');
            $is_valid_nonce = wp_verify_nonce($nonce, 'wp_rest');
            return current_user_can('subscriber') && $is_valid_nonce;
        },
    ));

    // Register route for listing chat rooms
    register_rest_route('wp-rest-chat/v1', '/rooms', array(
        'methods' => 'GET',
        'callback' => 'wp_rest_chat_list_rooms',
        'permission_callback' => function () {
            return current_user_can('administrator');
        },
    ));

    // Register route for getting messages
    register_rest_route('wp-rest-chat/v1', '/messages/(?P<room_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'wp_rest_chat_get_messages',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    // Register route for posting messages
    register_rest_route('wp-rest-chat/v1', '/messages/(?P<room_id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'wp_rest_chat_post_room_message',
        'permission_callback' => function ($request) {
            $nonce = $request->get_header('X-WP-Nonce');
            $is_valid_nonce = wp_verify_nonce($nonce, 'wp_rest');
            return is_user_logged_in() && $is_valid_nonce;
        },
    ));
});

// Function to create a chat room
function wp_rest_chat_create_room(WP_REST_Request $request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'chat_rooms';

    $wpdb->insert($table_name, array(
        'created_at' => current_time('mysql'),
    ));

    $room_id = $wpdb->insert_id;

    return new WP_REST_Response(array(
        'room_id' => $room_id,
    ), 200);
}

// Function to list chat rooms
function wp_rest_chat_list_rooms(WP_REST_Request $request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'chat_rooms';

    $rooms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

    $data = array();

    foreach ($rooms as $room) {
        $data[] = array(
            'id' => $room->id,
            'created_at' => $room->created_at,
        );
    }

    return new WP_REST_Response($data, 200);
}

// Function to get messages in a chat room
function wp_rest_chat_get_messages(WP_REST_Request $request) {
    global $wpdb;
    $room_id = $request['room_id'];
    $table_name = $wpdb->prefix . 'chat_messages';
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE room_id = %d ORDER BY timestamp DESC", $room_id));

    return new WP_REST_Response($results, 200);
}

// Function to post a message to a chat room
function wp_rest_chat_post_room_message(WP_REST_Request $request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'chat_messages';

    $data = json_decode($request->get_body(), true);
    $content = sanitize_text_field($data['content']);
    $user = sanitize_text_field($data['user']);
    $room_id = intval($data['room_id']);

    $wpdb->insert($table_name, array(
        'content' => $content,
        'user' => $user,
        'room_id' => $room_id,
        'timestamp' => current_time('mysql'),
    ));

    $message_id = $wpdb->insert_id;

    return new WP_REST_Response(array(
        'id' => $message_id,
        'content' => $content,
        'user' => $user,
        'room_id' => $room_id,
        'timestamp' => current_time('mysql'),
    ), 200);
}

// Create the chat rooms and messages tables on plugin activation
register_activation_hook(__FILE__, function () {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Create chat rooms table
    $table_name = $wpdb->prefix . 'chat_rooms';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Create chat messages table
    $table_name = $wpdb->prefix . 'chat_messages';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        content text NOT NULL,
        user varchar(255) NOT NULL,
        room_id mediumint(9) NOT NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    dbDelta($sql);
});
