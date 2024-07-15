<?php

use Firebase\JWT\JWT;

class WP_React_Chat_Plugin {

    public static function init() {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
        add_shortcode('react_chat', array(__CLASS__, 'render_chat_shortcode'));
        add_action('rest_api_init', array(__CLASS__, 'register_rest_routes'));
    }

   public static function enqueue_scripts() {
    wp_enqueue_script(
        'react-chat',
        plugin_dir_url(__FILE__) . '../public/js/chat.js',
        array('wp-element'),
        '1.0.0',
        true
    );

    // Localize script to pass data and nonce to the JavaScript
    wp_localize_script('react-chat', 'reactChat', array(
        'apiUrl' => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
        'user' => wp_get_current_user()->user_login,
        'token' => self::generate_jwt_token(wp_get_current_user()->ID),
    ));

    echo "<script>console.log('Script enqueued and localized');</script>";
}


    public static function render_chat_shortcode() {
        if (is_user_logged_in()) {
            echo "<script>console.log('User is logged in');</script>";
            return '<div id="react-chat-app"></div>';
        } else {
            echo "<script>console.log('User is not logged in');</script>";
            return '<p>You must be logged in to access the chat.</p>';
        }
    }

    public static function register_rest_routes() {
        register_rest_route('react-chat/v1', '/auth', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'authenticate_user'),
            'permission_callback' => '__return_true',
        ));
    }

    public static function authenticate_user(WP_REST_Request $request) {
        $creds = array(
            'user_login' => $request->get_param('username'),
            'user_password' => $request->get_param('password'),
            'remember' => true
        );

        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            return new WP_Error('invalid_credentials', 'Invalid username or password.', array('status' => 403));
        }

        $jwt = self::generate_jwt_token($user->ID);

        return array(
            'token' => $jwt,
            'user' => array(
                'id' => $user->ID,
                'username' => $user->user_login
            )
        );
    }

    private static $jwt_secret = 'Shoe1/2/3'; // Ensure this matches the Node.js server

    public static function generate_jwt_token($user_id) {
        $user_info = get_userdata($user_id);
        $username = $user_info->user_login;
    
        $issued_at = time();
        $expiration_time = $issued_at + (60 * 60 * 24); // jwt valid for 24 hours
        $payload = array(
            'iss' => get_site_url(),
            'iat' => $issued_at,
            'exp' => $expiration_time,
            'data' => array(
                'user' => array(
                    'id' => $user_id,
                    'username' => $username
                )
            )
        );
    
        $token = JWT::encode($payload, self::$jwt_secret, 'HS256');
        error_log('Generated JWT Token: ' . $token); // Log the token
        return $token;
    }
}


register_activation_hook(__FILE__, 'wp_react_chat_create_table');

function wp_react_chat_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'react_chat_messages';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        room_id bigint(20) NOT NULL,
        user_id bigint(20) NOT NULL,
        message text NOT NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
