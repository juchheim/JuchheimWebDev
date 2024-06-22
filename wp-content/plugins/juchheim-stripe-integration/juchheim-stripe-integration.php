<?php
/**
 * Plugin Name: Juchheim Stripe Integration
 * Description: Custom Stripe integration for Juchheim Web Development.
 * Version: 1.0
 * Author: Ernest Juchheim
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue Stripe.js and custom JS
function juchheim_enqueue_stripe_scripts() {
    wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/');
    wp_enqueue_script('stripe-custom-js', plugin_dir_url(__FILE__) . 'js/stripe.js', array('stripe-js'), null, true);
}
add_action('wp_enqueue_scripts', 'juchheim_enqueue_stripe_scripts');

// Shortcode for the payment forms
function juchheim_payment_forms_shortcode() {
    ob_start();
    ?>
    <div class="content active" id="web-hosting">
        <form id="web-hosting-form" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="POST">
            <input type="hidden" name="stripe_nonce" value="<?php echo wp_create_nonce('stripe_nonce'); ?>">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="plan">Choose your plan:</label>
            <select id="plan" name="plan">
                <option value="monthly">Monthly - $25</option>
                <option value="annually">Annually - $250</option>
            </select>

            <button type="submit">Submit</button>
            <input type="hidden" name="action" value="create_checkout_session">
        </form>
    </div>

    <div class="content" id="design-development">
        <form id="development-form" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="POST">
            <input type="hidden" name="stripe_nonce" value="<?php echo wp_create_nonce('stripe_nonce'); ?>">

            <label for="dev-name">Name:</label>
            <input type="text" id="dev-name" name="name" required>

            <label for="dev-email">Email:</label>
            <input type="email" id="dev-email" name="email" required>

            <label for="dev-password">Password:</label>
            <input type="password" id="dev-password" name="password" required>

            <label for="dev-plan">Choose your plan:</label>
            <select id="dev-plan" name="plan">
                <option value="10-page-no-sub">10-page (no sub pages) - $1000</option>
                <option value="10-page-with-sub">10-page (with sub pages) - $1500</option>
            </select>

            <button type="submit">Submit</button>
            <input type="hidden" name="action" value="create_checkout_session">
        </form>
    </div>

    <div class="content" id="custom">
        <form id="custom-form" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="POST">
            <input type="hidden" name="stripe_nonce" value="<?php echo wp_create_nonce('stripe_nonce'); ?>">

            <p class="custom-note">Choose this option if we've agreed to a price based on your unique needs. Interested in a quote? <a href="mailto:juchheim@gmail.com">Email me.</a></p>

            <label for="custom-name">Name:</label>
            <input type="text" id="custom-name" name="name" required>

            <label for="custom-email">Email:</label>
            <input type="email" id="custom-email" name="email" required>

            <label for="custom-password">Password:</label>
            <input type="password" id="custom-password" name="password" required>

            <label for="custom-price">Price:</label>
            <input type="number" id="custom-price" name="price" required>

            <button type="submit">Submit</button>
            <input type="hidden" name="action" value="create_checkout_session">
        </form>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('juchheim_payment_forms', 'juchheim_payment_forms_shortcode');

// Include the create checkout session script
function juchheim_create_checkout_session() {
    require_once plugin_dir_path(__FILE__) . 'includes/create-checkout-session.php';
}
add_action('wp_ajax_create_checkout_session', 'juchheim_create_checkout_session');
add_action('wp_ajax_nopriv_create_checkout_session', 'juchheim_create_checkout_session');

// Register REST API route
add_action('rest_api_init', function () {
    error_log('Registering custom REST API route for Stripe webhook'); // Debug logging
    register_rest_route('wpmm/v1', '/stripe-webhook', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'juchheim_stripe_webhook_handler',
    ));
});

// Webhook handler function
function juchheim_stripe_webhook_handler(WP_REST_Request $request) {
    error_log('Webhook handler called'); // Debug logging to confirm handler is called

    $payload = $request->get_body();
    $sig_header = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';
    $endpoint_secret = 'whsec_kNP7kmke4yorjL837t5vybbFzFjyxXSx';

    $event = null;

    // Verify the webhook signature
    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $endpoint_secret
        );
    } catch (\UnexpectedValueException $e) {
        error_log('Invalid payload: ' . $e->getMessage());
        return new WP_Error('invalid_payload', 'Invalid Payload', array('status' => 400));
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        error_log('Invalid signature: ' . $e->getMessage());
        return new WP_Error('invalid_signature', 'Invalid Signature', array('status' => 400));
    }

    // Handle the event type
    if ($event->type == 'checkout.session.completed') { // Change this to 'payment_intent.succeeded' if using Payment Intents
        $session = $event->data->object;

        // Retrieve metadata from the session
        $username = sanitize_text_field($session->metadata->username);
        $email = sanitize_email($session->metadata->email);
        $password = sanitize_text_field($session->metadata->password);

        // Create WordPress user if not exists
        if (!username_exists($username) && !email_exists($email)) {
            $user_id = wp_create_user($username, $password, $email);
            if (!is_wp_error($user_id)) {
                wp_update_user([
                    'ID' => $user_id,
                    'display_name' => $username,
                    'role' => 'subscriber'
                ]);

                error_log('User created: ' . $email);
            } else {
                error_log('User creation failed: ' . $user_id->get_error_message());
            }
        } else {
            error_log('User already exists: ' . $email);
        }
    } else {
        error_log('Unhandled event type: ' . $event->type);
    }

    return new WP_REST_Response('Webhook Handled', 200);
}

// Ensure Stripe PHP library is included
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
