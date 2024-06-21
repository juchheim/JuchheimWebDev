<?php
/*
Plugin Name: Stripe Integration
Description: A plugin to integrate Stripe payments.
Version: 1.0
Author: Ernest Juchheim
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Ensure environment variables are set
if (!getenv('STRIPE_PUBLISHABLE_KEY') || !getenv('STRIPE_SECRET_KEY') || !getenv('STRIPE_WEBHOOK_SECRET')) {
    error_log('Environment variables are not set properly.');
    die('Environment variables are not set properly.');
}

// Enqueue scripts and styles
function stripe_integration_enqueue_scripts() {
    if (!wp_script_is('stripe', 'enqueued')) {
        wp_enqueue_script('stripe', 'https://js.stripe.com/v3/');
    }

    wp_enqueue_style('stripe-integration-style', plugin_dir_url(__FILE__) . 'assets/css/stripe-integration.css');
    wp_enqueue_script('stripe-integration-script', plugin_dir_url(__FILE__) . 'assets/js/stripe-integration.js', array('jquery', 'stripe'), null, true);

    // Localize script to pass data from PHP to JS
    wp_localize_script('stripe-integration-script', 'stripeIntegration', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'stripe_nonce' => wp_create_nonce('stripe_nonce'),
        'stripe_publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY')
    ));
}
add_action('wp_enqueue_scripts', 'stripe_integration_enqueue_scripts');

function stripe_integration_display_forms() {
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
add_shortcode('stripe_integration_forms', 'stripe_integration_display_forms');

// Handle creating a Stripe Checkout Session
function create_stripe_checkout_session() {
    check_ajax_referer('stripe_nonce', 'nonce');

    $form_data = $_POST['formData'];
    parse_str($form_data, $form_array);

    $name = sanitize_text_field($form_array['name']);
    $email = sanitize_email($form_array['email']);
    $password = sanitize_text_field($form_array['password']);
    $plan = sanitize_text_field($form_array['plan']);
    $price_id = '';

    $mode = 'payment'; // Default to one-time payment
    $line_items = [];

    // Determine the price ID based on the plan and form
    if ($plan === 'monthly') {
        $price_id = 'price_1PTTKAHrZfxkHCcnPB3l0Cbc'; // Update with your actual price ID for monthly plan
        $mode = 'subscription';
    } elseif ($plan === 'annually') {
        $price_id = 'price_1PTToQHrZfxkHCcntMWJbMkM'; // Update with your actual price ID for annual plan
        $mode = 'subscription';
    } elseif ($plan === '10-page-no-sub') {
        $price_id = 'price_1PTnmnHrZfxkHCcnBjcSLQad'; // Update with your actual price ID for 10-page-no-sub plan
    } elseif ($plan === '10-page-with-sub') {
        $price_id = 'price_1PTnnKHrZfxkHCcnZ8k8UCcE'; // Update with your actual price ID for 10-page-with-sub plan
    } else {
        $price = sanitize_text_field($form_array['price']);
        $line_items[] = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Custom Service',
                ],
                'unit_amount' => $price * 100, // Convert to cents
            ],
            'quantity' => 1,
        ];
    }

    if (empty($line_items)) {
        $line_items[] = [
            'price' => $price_id,
            'quantity' => 1,
        ];
    }

    \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

    try {
        // Create a Checkout Session
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'customer_email' => $email,
            'line_items' => $line_items,
            'mode' => $mode,
            'success_url' => home_url('/checkout-success/?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => home_url('/checkout-cancelled/'),
            'metadata' => [
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ],
        ]);

        wp_send_json_success(['sessionId' => $session->id]);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_create_stripe_checkout_session', 'create_stripe_checkout_session');
add_action('wp_ajax_nopriv_create_stripe_checkout_session', 'create_stripe_checkout_session');

// Register the webhook endpoint
add_action('rest_api_init', function () {
    register_rest_route('stripe/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'stripe_webhook_handler',
        'permission_callback' => '__return_true', // Adjust permissions as needed
    ));
});

// Webhook handler function
function stripe_webhook_handler(WP_REST_Request $request) {
    $payload = $request->get_body();
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    $endpoint_secret = getenv('STRIPE_WEBHOOK_SECRET');

    // Log the payload for debugging
    error_log('Stripe Webhook Payload: ' . $payload);

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $endpoint_secret
        );
    } catch (\UnexpectedValueException $e) {
        // Invalid payload
        error_log('Invalid Payload: ' . $e->getMessage());
        return new WP_Error('invalid_payload', 'Invalid payload', array('status' => 400));
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        error_log('Invalid Signature: ' . $e->getMessage());
        return new WP_Error('invalid_signature', 'Invalid signature', array('status' => 400));
    }

    // Handle the event
    switch ($event->type) {
        case 'checkout.session.completed':
            $session = $event->data->object;
            handle_checkout_session_completed($session);
            break;
        default:
            // Unexpected event type
            error_log('Unexpected Event Type: ' . $event->type);
            return new WP_Error('unexpected_event', 'Unexpected event type', array('status' => 400));
    }

    return new WP_REST_Response('Webhook received', 200);
}

// Function to handle successful checkout session
function handle_checkout_session_completed($session) {
    // Log the session object for debugging
    error_log('Stripe Checkout Session: ' . print_r($session, true));

    // Extract relevant information from $session
    $customer_id = $session->customer;
    $metadata = $session->metadata;

    $name = $metadata->name;
    $email = $metadata->email;
    $password = $metadata->password;

    // Check if the user already exists
    if (!email_exists($email)) {
        // Create a new user
        $user_id = wp_create_user($email, $password, $email);

        if (!is_wp_error($user_id)) {
            // Update user metadata with additional information
            update_user_meta($user_id, 'customer_id', $customer_id);
            // Optionally update other user details, such as the display name
            wp_update_user([
                'ID' => $user_id,
                'display_name' => $name,
                'first_name' => $name, // Split name if necessary
            ]);

            // Optionally send a confirmation email to the new user
            wp_new_user_notification($user_id, null, 'both');
        } else {
            error_log('User creation failed: ' . $user_id->get_error_message());
        }
    } else {
        // User already exists, update user details or log an error
        error_log('User with email ' . $email . ' already exists.');
    }
}
