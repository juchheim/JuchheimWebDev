<?php
/**
 * Plugin Name: Juchheim Stripe Integration
 * Description: A simple Stripe integration for Juchheim Web Development.
 * Version: 1.0
 * Author: Ernest Juchheim
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Include the Composer autoload file
require_once __DIR__ . '/vendor/autoload.php';

// Define constants.
define('JUCHHEIM_STRIPE_VERSION', '1.0');

// Enqueue scripts and styles.
function juchheim_enqueue_scripts() {
    wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/');
    wp_enqueue_script('juchheim-stripe', plugins_url('/js/juchheim-stripe.js', __FILE__), array('jquery', 'stripe-js'), JUCHHEIM_STRIPE_VERSION, true);
    wp_localize_script('juchheim-stripe', 'juchheimStripe', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'stripe_nonce' => wp_create_nonce('stripe_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'juchheim_enqueue_scripts');

// Shortcode to display forms
function juchheim_display_forms() {
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
add_shortcode('juchheim_stripe_forms', 'juchheim_display_forms');

// Handle form submission via AJAX
function juchheim_handle_form_submission() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'stripe_nonce')) {
        wp_send_json_error(['message' => 'Nonce verification failed']);
    }

    $form_data = $_POST['form_data'];

    // Get form data
    $name = sanitize_text_field($form_data['name']);
    $email = sanitize_email($form_data['email']);
    $password = sanitize_text_field($form_data['password']);
    $plan = isset($form_data['plan']) ? sanitize_text_field($form_data['plan']) : '';
    $price = isset($form_data['price']) ? floatval($form_data['price']) : 0;

    // Validate form data
    if (empty($name) || empty($email) || empty($password)) {
        wp_send_json_error(['message' => 'Required fields are missing']);
    }

    // Debugging information
    error_log("Name: $name, Email: $email, Plan: $plan, Price: $price");

    // Set Stripe API key
    \Stripe\Stripe::setApiKey('sk_test_51PRj4aHrZfxkHCcnjYNK7r3Ev1e1sIlU4R3itbutVSG1fJKAzfEOehjvFZz7B9A8v5Hu0fF0Dh9sv5ZYmbrd9swh00VLTD1J2Q');

    try {
        // Determine amount based on plan or custom price
        $amount = ($plan === 'monthly') ? 2500 : (($plan === 'annually') ? 25000 : $price * 100);

        // Create a Stripe Checkout session
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'customer_email' => $email,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Payment for ' . ($plan ? $plan : 'Custom Price'),
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => site_url('/checkout-success/?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => site_url('/checkout-cancel/'),
        ]);

        wp_send_json_success(['session_id' => $session->id]);
    } catch (Exception $e) {
        error_log('Payment failed: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Payment failed: ' . $e->getMessage()]);
    }
}
add_action('wp_ajax_juchheim_handle_form', 'juchheim_handle_form_submission');
add_action('wp_ajax_nopriv_juchheim_handle_form', 'juchheim_handle_form_submission');
