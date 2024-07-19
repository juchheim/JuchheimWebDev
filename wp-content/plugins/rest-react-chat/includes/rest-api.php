<?php

add_action('rest_api_init', function() {
    register_rest_route('chat/v1', '/messages', array(
        'methods' => 'GET',
        'callback' => 'get_chat_messages',
    ));

    register_rest_route('chat/v1', '/messages', array(
        'methods' => 'POST',
        'callback' => 'post_chat_message',
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ));
});

function get_chat_messages() {
    $messages = get_option('wp_rest_chat_messages', array());
    return rest_ensure_response($messages);
}

function post_chat_message($request) {
    $message = $request->get_param('message');
    $user = wp_get_current_user();

    if (empty($message) || !$user->exists()) {
        return new WP_Error('invalid_data', 'Invalid message or user', array('status' => 400));
    }

    $messages = get_option('wp_rest_chat_messages', array());
    $messages[] = array(
        'user' => $user->user_login,
        'message' => $message,
        'timestamp' => current_time('mysql'),
    );

    update_option('wp_rest_chat_messages', $messages);

    return rest_ensure_response($messages);
}
