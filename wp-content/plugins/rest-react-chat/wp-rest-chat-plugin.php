<?php
/**
 * Plugin Name: WP REST/React Chat Plugin
 * Description: A simple chat plugin using the WordPress REST API.
 * Version: 1.0.0
 * Author: Your Name
 */

// Register REST API routes
add_action('rest_api_init', function () {
    register_rest_route('chat/v1', '/messages/(?P<room_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_chat_messages',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route('chat/v1', '/messages/(?P<room_id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'send_chat_message',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route('chat/v1', '/rooms', array(
        'methods' => 'POST',
        'callback' => 'create_chat_room',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route('chat/v1', '/rooms', array(
        'methods' => 'GET',
        'callback' => 'get_chat_rooms',
        'permission_callback' => function () {
            return current_user_can('administrator');
        },
    ));

    error_log('Registered REST API routes for chat/v1');
});

function get_chat_messages($request) {
    error_log('Fetching chat messages...');
    global $wpdb;
    $room_id = $request['room_id'];
    $table_name = $wpdb->prefix . 'chat_messages';
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE room_id = %d ORDER BY timestamp ASC", $room_id));
    error_log('Fetched messages: ' . json_encode($results));
    return $results;
}

function create_chat_room(WP_REST_Request $request) {
    error_log('Creating new chat room...');
    global $wpdb;
    $table_name = $wpdb->prefix . 'chat_rooms';
    $wpdb->insert($table_name, array(
        'created_at' => current_time('mysql'),
    ));
    $room_id = $wpdb->insert_id;
    error_log('Created new chat room: ' . $room_id);
    return array('room_id' => $room_id);
}

function get_chat_rooms() {
    error_log('Fetching chat rooms...');
    global $wpdb;
    $table_name = $wpdb->prefix . 'chat_rooms';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
    error_log('Fetched chat rooms: ' . json_encode($results));
    return $results;
}


function send_chat_message(WP_REST_Request $request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'chat_messages';

    $data = json_decode($request->get_body(), true);
    $content = sanitize_text_field($data['content']);
    $user = sanitize_text_field($data['user']);
    $room_id = intval($data['room_id']);
    error_log('Sending message to room: ' . $room_id . ' User: ' . $user);

    $wpdb->insert($table_name, array(
        'content' => $content,
        'user' => $user,
        'room_id' => $room_id,
        'timestamp' => current_time('mysql'),
    ));

    $message_id = $wpdb->insert_id;
    error_log('Sent message ID: ' . $message_id);

    return array(
        'id' => $message_id,
        'content' => $content,
        'user' => $user,
        'room_id' => $room_id,
        'timestamp' => current_time('mysql'),
    );
}

// Register the admin menu
function register_chat_admin_page() {
    add_menu_page(
        'Chat Rooms',  // Page title
        'Chat Rooms',  // Menu title
        'manage_options',  // Capability
        'chat-rooms',  // Menu slug
        'display_chat_admin_page',  // Function to display the content
        'dashicons-admin-comments',  // Icon
        6  // Position
    );
}
add_action('admin_menu', 'register_chat_admin_page');

// Display the admin page content
function display_chat_admin_page() {
    echo '<div id="admin-dashboard"></div>';
}

// Enqueue the admin script
function enqueue_admin_scripts($hook_suffix) {
    if ($hook_suffix !== 'toplevel_page_chat-rooms') {
        return;
    }

    wp_enqueue_script('wp-rest-chat-admin', plugins_url('admin.js', __FILE__), array(), '1.0.0', true);
    wp_localize_script('wp-rest-chat-admin', 'wpRestChat', array(
        'apiUrl' => esc_url_raw(rest_url('chat/v1/')),
        'user' => wp_get_current_user()->user_login,
        'nonce' => wp_create_nonce('wp_rest'),
    ));
}
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');

// Enqueue the React app
add_action('wp_enqueue_scripts', function () {
    // Enqueue the main.js file dynamically
    $build_dir = plugin_dir_path(__FILE__) . 'react-chat-frontend/build/static/js';
    $files = scandir($build_dir);
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
        wp_localize_script('wp-rest-chat-frontend', 'wpRestChat', array(
            'apiUrl' => esc_url_raw(rest_url('chat/v1/')),
            'user' => wp_get_current_user()->user_login,
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    } else {
        error_log('Error: main.js file not found in build directory.');
    }
});

// Shortcode to render the chat app
function wp_rest_chat_shortcode() {
    if (is_user_logged_in()) {
        error_log('User logged in, displaying chat app.');
        return '<div id="wp-rest-chat-app"></div>';
    } else {
        error_log('User not logged in.');
        return '<p>Please log in to access the chat.</p>';
    }
}
add_shortcode('wp_rest_chat', 'wp_rest_chat_shortcode');

// Create the chat messages and rooms tables on plugin activation
register_activation_hook(__FILE__, function () {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

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

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Create chat rooms table
    $table_name = $wpdb->prefix . 'chat_rooms';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    dbDelta($sql);
});

function chat_rooms_admin_page() {
    ?>
    <div id="admin-root"></div>
    <script src="<?php echo plugin_dir_url(__FILE__); ?>react-chat-frontend/build/static/js/admin.js"></script>
    <?php
}

// Example logging function
function log_to_console($message) {
    if (is_array($message) || is_object($message)) {
        echo("<script>console.log('PHP: " . json_encode($message) . "');</script>");
    } else {
        echo("<script>console.log('PHP: " . $message . "');</script>");
    }
}

// Usage example
log_to_console('Chat room route accessed.');
