<?php
/**
 * Plugin Name: Chat Plugin
 * Description: A plugin to add real-time chat functionality.
 * Version: 1.0
 * Author: Your Name
 */

// Hook to create tables when the plugin is activated
register_activation_hook( __FILE__, 'chat_plugin_create_tables' );

/**
 * Function to create chat tables.
 */
function chat_plugin_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table names
    $table_chats = $wpdb->prefix . 'chat_chats';
    $table_messages = $wpdb->prefix . 'chat_messages';

    // SQL to create tables
    $sql = "
    CREATE TABLE $table_chats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;

    CREATE TABLE $table_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chat_id INT,
        user_id BIGINT(20) UNSIGNED,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (chat_id) REFERENCES $table_chats(id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID)
    ) $charset_collate;
    ";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// Register REST API endpoint for creating chat messages
add_action( 'rest_api_init', function () {
    register_rest_route( 'chat/v1', '/messages', array(
        'methods' => 'POST',
        'callback' => 'create_chat_message',
    ) );
});

/**
 * Callback function to handle creating chat messages.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error The response or WP_Error on failure.
 */
function create_chat_message( $request ) {
    global $wpdb;
    $table = $wpdb->prefix . 'chat_messages';

    // Get parameters from the request
    $chat_id = $request->get_param( 'chat_id' );
    $user_id = $request->get_param( 'user_id' );
    $message = $request->get_param( 'message' );
    $created_at = current_time( 'mysql' );

    // Log the received data for debugging
    error_log( "Received data: chat_id={$chat_id}, user_id={$user_id}, message={$message}" );

    // Prepare data for insertion
    $data = array(
        'chat_id'    => $chat_id,
        'user_id'    => $user_id,
        'message'    => $message,
        'created_at' => $created_at,
    );

    // Data format
    $format = array(
        '%d',
        '%d',
        '%s',
        '%s',
    );

    // Insert data into the database
    $inserted = $wpdb->insert( $table, $data, $format );

    // Check if the insertion was successful
    if ( $inserted ) {
        return new WP_REST_Response( 'Message sent', 200 );
    } else {
        return new WP_Error( 'db_insert_error', 'Could not insert message', array( 'status' => 500 ) );
    }
}

function get_chat_messages(WP_REST_Request $request) {
    global $wpdb;
    $chat_id = $request['chat_id'];

    $messages = $wpdb->get_results($wpdb->prepare("
        SELECT m.*, u.user_login as user_name
        FROM {$wpdb->prefix}chat_messages m
        JOIN {$wpdb->prefix}users u ON m.user_id = u.ID
        WHERE m.chat_id = %d
        ORDER BY m.created_at ASC
    ", $chat_id));

    return new WP_REST_Response($messages, 200);
}

add_action('rest_api_init', function () {
    register_rest_route('chat/v1', '/messages', [
        'methods' => 'GET',
        'callback' => 'get_chat_messages',
        'args' => [
            'chat_id' => [
                'required' => true,
            ],
        ],
    ]);
});
