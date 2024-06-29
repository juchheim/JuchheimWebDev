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
    $email = sanitize_email($form_data['email']); // ensure email is included

    if ($form_id === 'web-hosting-form') {
        $price_id = ($plan_type === 'monthly') ? 'price_1PTpZBHrZfxkHCcnbQRzh5rL' : 'price_1PTpZoHrZfxkHCcnmwDV0mXm';
        $mode = 'subscription';
    } elseif ($form_id === 'development-form') {
        $price_id = ($plan_type === '10-page-no-sub') ? 'price_1PTq1QHrZfxkHCcnjMmehUOX' : 'price_1PTpbmHrZfxkHCcnkCPJz1ce';
    } elseif ($form_id === 'custom-form') {
        $price = floatval($form_data['price']) * 100; // convert to cents
    }

    \Stripe\Stripe::setApiKey('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');

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
                'customer_email' => $email,
                'mode' => 'payment',
                'success_url' => site_url('/checkout-success'),
                'cancel_url' => site_url('/checkout-cancelled'),
                'metadata' => [
                    'name' => sanitize_text_field($form_data['name']),
                    'password' => sanitize_text_field($form_data['password']),
                ],
            ]);
        } else {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $price_id,
                    'quantity' => 1,
                ]],
                'customer_email' => $email,
                'mode' => $mode,
                'success_url' => site_url('/checkout-success'),
                'cancel_url' => site_url('/checkout-cancelled'),
                'metadata' => [
                    'name' => sanitize_text_field($form_data['name']),
                    'password' => sanitize_text_field($form_data['password']),
                ],
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

            <label for="dev-plan">Choose your development option:</label>
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

    \Stripe\Stripe::setApiKey('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');

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
                'customer_email' => $email,
                'mode' => 'payment',
                'success_url' => site_url('/checkout-success/?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => site_url('/checkout-cancel/'),
                'metadata' => [
                    'name' => $name,
                    'password' => $password,
                ],
            ]);
        } else {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $price_id,
                    'quantity' => 1,
                ]],
                'customer_email' => $email,
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
            $payload, $sig_header, 'whsec_JCCeY0rrfJPkbyYlAOPsmpoW8nR5Phg0'
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
            $product_name = isset($session['display_items'][0]['custom']['name']) ? $session['display_items'][0]['custom']['name'] : 'Custom Payment';

            if (email_exists($customer_email) == false) {
                $user_id = wp_create_user($name, $password, $customer_email);

                if (is_wp_error($user_id)) {
                    error_log('User creation failed: ' . $user_id->get_error_message());
                } else {
                    wp_update_user(array('ID' => $user_id, 'display_name' => $name));
                    error_log("User created successfully: user_id=$user_id");

                    // Send an email notification using wp_mail()
                    $to = 'ernest@juchheim.online';
                    $subject = 'New User Registration';
                    $message = "A new user has registered:\n\n";
                    $message .= "Name: $name\n";
                    $message .= "Email: $customer_email\n";
                    $message .= "Product Purchased: $product_name\n";
                    $headers = array('Content-Type: text/plain; charset=UTF-8');
                    if (wp_mail($to, $subject, $message, $headers)) {
                        error_log('Notification email sent successfully.');
                    } else {
                        error_log('Failed to send notification email.');
                    }
                }
            } else {
                error_log('User already exists: ' . $customer_email);

                // Send an email notification using wp_mail() even if user exists
                $to = 'juchheim@gmail.com'; // Changed to send to juchheim@gmail.com
                $subject = 'Purchase Notification';
                $message = "An existing user has made a purchase:\n\n";
                $message .= "Name: $name\n";
                $message .= "Email: $customer_email\n";
                $message .= "Product Purchased: $product_name\n";
                $headers = array('Content-Type: text/plain; charset=UTF-8');
                if (wp_mail($to, $subject, $message, $headers)) {
                    error_log('Notification email sent successfully.');
                } else {
                    error_log('Failed to send notification email.');
                }
            }

            break;
        default:
            error_log('Unexpected event type: ' . $event['type']);
            return new WP_Error('unexpected_event_type', 'Unexpected event type', array('status' => 400));
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
