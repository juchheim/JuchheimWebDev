<?php
/**
 * Plugin Name: Juchheim Stripe Integration
 * Description: A simple Stripe integration for Juchheim Web Development.
 * Version: 1.0
 * Author: Ernest Juchheim
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

define('JUCHHEIM_STRIPE_VERSION', '1.0');

function juchheim_enqueue_scripts() {
    wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/');
    wp_enqueue_script('juchheim-stripe', plugins_url('/js/juchheim-stripe.js', __FILE__), array('jquery', 'stripe-js'), JUCHHEIM_STRIPE_VERSION, true);
    wp_localize_script('juchheim-stripe', 'juchheimStripe', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'stripe_nonce' => wp_create_nonce('stripe_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'juchheim_enqueue_scripts');

add_action('wp_ajax_juchheim_handle_form', 'juchheim_handle_form');
add_action('wp_ajax_nopriv_juchheim_handle_form', 'juchheim_handle_form');

function juchheim_handle_form() {
    check_ajax_referer('stripe_nonce', 'nonce');

    $form_data = $_POST['form_data'];
    $plan_type = sanitize_text_field($form_data['plan_type']);
    $form_id = sanitize_text_field($form_data['form_id']);
    $price_id = '';
    $mode = 'payment'; // default to one-time payment

    if ($form_id === 'web-hosting-form') {
        $price_id = ($plan_type === 'monthly') ? 'price_1PTTKAHrZfxkHCcnPB3l0Cbc' : 'price_1PTToQHrZfxkHCcntMWJbMkM';
        $mode = 'subscription';
    } elseif ($form_id === 'development-form') {
        $price_id = ($plan_type === '10-page-no-sub') ? 'price_1PTnmnHrZfxkHCcnBjcSLQad' : 'price_1PTnnKHrZfxkHCcnZ8k8UCcE';
    } elseif ($form_id === 'custom-form') {
        $price = floatval($form_data['price']) * 100; // convert to cents
    }

    \Stripe\Stripe::setApiKey('sk_test_51PRj4aHrZfxkHCcnjYNK7r3Ev1e1sIlU4R3itbutVSG1fJKAzfEOehjvFZz7B9A8v5Hu0fF0Dh9sv5ZYmbrd9swh00VLTD1J2Q');

    try {
        if ($form_id === 'custom-form') {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => 'Custom Payment'],
                        'unit_amount' => $price,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => site_url('/success'),
                'cancel_url' => site_url('/cancel'),
            ]);
        } else {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $price_id,
                    'quantity' => 1,
                ]],
                'mode' => $mode,
                'success_url' => site_url('/success'),
                'cancel_url' => site_url('/cancel'),
            ]);
        }

        wp_send_json_success(array('session_id' => $session->id));
    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}

function juchheim_display_forms() {
    ob_start();
    ?>
    <div class="content active" id="web-hosting">
        <form id="web-hosting-form">
            <input type="hidden" name="stripe_nonce" value="<?php echo wp_create_nonce('stripe_nonce'); ?>">
            <input type="hidden" name="form_id" value="web-hosting-form">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="plan">Choose your plan:</label>
            <select id="plan" name="plan_type">
                <option value="monthly">Monthly - $25</option>
                <option value="annual">Annually - $250</option>
            </select>

            <button type="submit">Submit</button>
        </form>
    </div>
    <div class="content" id="design-development">
        <form id="development-form">
            <input type="hidden" name="stripe_nonce" value="<?php echo wp_create_nonce('stripe_nonce'); ?>">
            <input type="hidden" name="form_id" value="development-form">

            <label for="dev-name">Name:</label>
            <input type="text" id="dev-name" name="name" required>

            <label for="dev-email">Email:</label>
            <input type="email" id="dev-email" name="email" required>

            <label for="dev-password">Password:</label>
            <input type="password" id="dev-password" name="password" required>

            <label for="dev-plan">Choose your plan:</label>
            <select id="dev-plan" name="plan_type">
                <option value="10-page-no-sub">10-page (no sub pages) - $1000</option>
                <option value="10-page-with-sub">10-page (with sub pages) - $1500</option>
            </select>

            <button type="submit">Submit</button>
        </form>
    </div>
    <div class="content" id="custom">
        <form id="custom-form">
            <input type="hidden" name="stripe_nonce" value="<?php echo wp_create_nonce('stripe_nonce'); ?>">
            <input type="hidden" name="form_id" value="custom-form">

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

function juchheim_handle_form_submission() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'stripe_nonce')) {
        wp_send_json_error(['message' => 'Nonce verification failed']);
    }

    $form_data = $_POST['form_data'];
    $name = sanitize_text_field($form_data['name']);
    $email = sanitize_email($form_data['email']);
    $password = sanitize_text_field($form_data['password']);
    $plan_type = isset($form_data['plan_type']) ? sanitize_text_field($form_data['plan_type']) : '';
    $price = isset($form_data['price']) ? floatval($form_data['price']) * 100 : 0; // Convert price to cents
    $form_id = sanitize_text_field($form_data['form_id']);
    $price_id = '';
    $mode = 'payment';

    if ($form_id === 'web-hosting-form') {
        $price_id = ($plan_type === 'monthly') ? 'price_1PTTKAHrZfxkHCcnPB3l0Cbc' : 'price_1PTToQHrZfxkHCcntMWJbMkM';
        $mode = 'subscription';
    } elseif ($form_id === 'development-form') {
        $price_id = ($plan_type === '10-page-no-sub') ? 'price_1PTnmnHrZfxkHCcnBjcSLQad' : 'price_1PTnnKHrZfxkHCcnZ8k8UCcE';
    }

    \Stripe\Stripe::setApiKey('sk_test_51PRj4aHrZfxkHCcnjYNK7r3Ev1e1sIlU4R3itbutVSG1fJKAzfEOehjvFZz7B9A8v5Hu0fF0Dh9sv5ZYmbrd9swh00VLTD1J2Q');

    try {
        if ($form_id === 'custom-form') {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => 'Custom Payment'],
                        'unit_amount' => $price,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => site_url('/checkout-success/?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => site_url('/checkout-cancel/'),
            ]);
        } else {
            $session = \Stripe\Checkout.Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $price_id,
                    'quantity' => 1,
                ]],
                'mode' => $mode,
                'success_url' => site_url('/checkout-success/?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => site_url('/checkout-cancel/'),
                'metadata' => [
                    'name' => $name,
                    'password' => $password,
                ],
            ]);
        }

        wp_send_json_success(['session_id' => $session->id]);
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Payment failed: ' . $e->getMessage()]);
    }
}
add_action('wp_ajax_juchheim_handle_form', 'juchheim_handle_form_submission');
add_action('wp_ajax_nopriv_juchheim_handle_form', 'juchheim_handle_form_submission');

// Webhook handler
function juchheim_stripe_webhook() {
    $payload = @file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    $event = null;

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, 'whsec_FvNAupwXBRlM8JCgu1nefxOeBCqU2wAo'
        );
    } catch (\UnexpectedValueException $e) {
        http_response_code(400);
        exit();
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        http_response_code(400);
        exit();
    }

    switch ($event['type']) {
        case 'checkout.session.completed':
            $session = $event['data']['object'];
            $customer_email = $session['customer_details']['email'];
            $name = $session['metadata']['name'];
            $password = $session['metadata']['password'];

            if (email_exists($customer_email) == false) {
                $user_id = wp_create_user($name, $password, $customer_email);

                if (is_wp_error($user_id)) {
                    error_log('User creation failed: ' . $user_id->get_error_message());
                } else {
                    wp_update_user(array('ID' => $user_id, 'display_name' => $name));
                }
            }
            break;
        default:
            echo 'Received unknown event type ' . $event['type'];
    }

    http_response_code(200);
}
add_action('rest_api_init', function() {
    register_rest_route('juchheim-stripe/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'juchheim_stripe_webhook',
    ));
});
?>
