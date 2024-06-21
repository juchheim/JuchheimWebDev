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
        <form id="web-hosting-form">
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
        </form>
    </div>
    <div class="content" id="design-development">
        <form id="development-form">
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
        </form>
    </div>
    <div class="content" id="custom">
        <form id="custom-form">
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

// Handle Stripe webhook
add_action('rest_api_init', function () {
    register_rest_route('stripe/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'juchheim_stripe_webhook_handler',
    ));
});

function juchheim_stripe_webhook_handler(WP_REST_Request $request) {
    $payload = $request->get_body();
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    $endpoint_secret = 'whsec_1zqdBkrvY225jlDKtOrQChjPuYacs700';

    $event = null;

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $endpoint_secret
        );
    } catch (\UnexpectedValueException $e) {
        // Invalid payload
        return new WP_Error('invalid_payload', 'Invalid Payload', array('status' => 400));
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        return new WP_Error('invalid_signature', 'Invalid Signature', array('status' => 400));
    }

    if ($event->type == 'checkout.session.completed') {
        $session = $event->data->object;

        // Retrieve session details
        $metadata = $session->metadata;
        $email = $metadata->email;
        $password = $metadata->password;
        $name = $metadata->name;

        // Create WordPress user
        $user_id = wp_create_user($email, $password, $email);
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $name,
            'role' => 'subscriber'
        ]);
    }

    return new WP_REST_Response('Webhook Handled', 200);
}
