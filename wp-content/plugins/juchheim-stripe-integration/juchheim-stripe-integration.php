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
        if ($plan_type === 'monthly') {
            $price_id = 'price_1PTpZBHrZfxkHCcnbQRzh5rL';
        }/* elseif ($plan_type === 'new-monthly') {
            $price_id = 'price_1PXr3XHrZfxkHCcnlNAxUtuK';
        }*/ else {
            $price_id = 'price_1PTpZoHrZfxkHCcnmwDV0mXm';
        }
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
        <!--    <option value="new-monthly">Testing - $0</option>   -->
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

            <p class="custom-note">Choose this option if we've agreed to a price based on your unique needs. Interested in a quote? <a href="mailto:ernest@juchheim.online">Email me.</a></p>

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
        if ($plan_type === 'monthly') {
            $price_id = 'price_1PTTKAHrZfxkHCcnPB3l0Cbc';
        } elseif ($plan_type === 'new-monthly') {
            $price_id = 'price_1PXr3XHrZfxkHCcnlNAxUtuK';
        } else {
            $price_id = 'price_1PTToQHrZfxkHCcntMWJbMkM';
        }
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

// Display custom fields in user profile
function juchheim_show_extra_profile_fields($user) {
    // Retrieve the stored values
    $products_purchased = get_the_author_meta('products_purchased', $user->ID);
    $products_purchased = $products_purchased ? unserialize($products_purchased) : [];

    ?>
    <h3><?php _e('Extra Profile Information', 'your-text-domain'); ?></h3>

    <table class="form-table">
        <tr>
            <th><label for="product_purchased"><?php _e('Product Purchased', 'your-text-domain'); ?></label></th>
            <td>
                <?php if (!empty($products_purchased)): ?>
                    <?php foreach ($products_purchased as $index => $purchase): ?>
                        <div>
                            <input type="text" name="products_purchased[<?php echo $index; ?>][product_name]" value="<?php echo esc_attr($purchase['product_name']); ?>" class="regular-text" placeholder="Product Name" />
                            <input type="text" name="products_purchased[<?php echo $index; ?>][purchase_price]" value="<?php echo esc_attr($purchase['purchase_price']); ?>" class="regular-text" placeholder="Purchase Price" />
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div id="extra-fields"></div>
                <button type="button" id="add-field"><?php _e('Add Another Product', 'your-text-domain'); ?></button>
                <span class="description"><?php _e('Add products purchased and their prices.', 'your-text-domain'); ?></span>
            </td>
        </tr>
    </table>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#add-field').click(function() {
                var index = $('#extra-fields div').length;
                $('#extra-fields').append(
                    '<div>' +
                        '<input type="text" name="products_purchased[' + index + '][product_name]" class="regular-text" placeholder="Product Name" />' +
                        '<input type="text" name="products_purchased[' + index + '][purchase_price]" class="regular-text" placeholder="Purchase Price" />' +
                    '</div>'
                );
            });
        });
    </script>
    <?php
}
add_action('show_user_profile', 'juchheim_show_extra_profile_fields');
add_action('edit_user_profile', 'juchheim_show_extra_profile_fields');

// Save custom fields in user profile
function juchheim_save_extra_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    if (isset($_POST['products_purchased']) && is_array($_POST['products_purchased'])) {
        $products_purchased = array_map(function($product) {
            return [
                'product_name' => sanitize_text_field($product['product_name']),
                'purchase_price' => sanitize_text_field($product['purchase_price']),
            ];
        }, $_POST['products_purchased']);

        update_user_meta($user_id, 'products_purchased', serialize($products_purchased));
    }
}
add_action('personal_options_update', 'juchheim_save_extra_profile_fields');
add_action('edit_user_profile_update', 'juchheim_save_extra_profile_fields');

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
            $amount_total = $session['amount_total'] / 100; // Amount is in cents, convert to dollars

            // Check if the user already exists
            $user = get_user_by('email', $customer_email);
            if (!$user) {
                // Create the user if they don't exist
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
                    $message .= "Amount: \$$amount_total\n";
                    $headers = array('Content-Type: text/plain; charset=UTF-8');
                    if (wp_mail($to, $subject, $message, $headers)) {
                        error_log('Notification email sent successfully.');
                    } else {
                        error_log('Failed to send notification email.');
                    }
                }
            } else {
                error_log('User already exists: ' . $customer_email);
                $user_id = $user->ID;
            }

            // Update user meta with multiple products purchased
            $purchased_products = get_user_meta($user_id, 'products_purchased', true);
            if (!$purchased_products) {
                $purchased_products = [];
            } else {
                $purchased_products = unserialize($purchased_products);
            }
            $purchased_products[] = [
                'product_name' => $product_name,
                'purchase_price' => $amount_total,
                'date' => current_time('mysql')
            ];
            update_user_meta($user_id, 'products_purchased', serialize($purchased_products));

            // Save customer information to Pods with published status
            $pod = pods('customer');
            $pod_id = $pod->add(array(
                'post_title' => $name,
                'post_status' => 'publish',
                'customer_name' => $name,
                'email' => $customer_email,
                'product_purchased' => $product_name,
                'price' => $amount_total,
            ));

            if (is_wp_error($pod_id)) {
                error_log('Failed to save customer to Pods: ' . $pod_id->get_error_message());
            } else {
                error_log("Customer saved to Pods successfully: pod_id=$pod_id");
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

// Add shortcode to display subscriptions
function juchheim_display_subscriptions() {
    if (!is_user_logged_in()) {
        return '<h3>You need to be logged in to view this page.</h3>';
    }

    $user_id = get_current_user_id();
    $customer_email = wp_get_current_user()->user_email;
    
    ob_start();
    ?>
    <h3>Your Hosting Plan</h3>
    <div id="subscriptions"></div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'juchheim_fetch_subscriptions',
                    email: '<?php echo $customer_email; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        var subscriptions = response.data;
                        var html = '<ul>';
                        subscriptions.forEach(function(subscription) {
                            html += '<li>' + subscription.product + ' - ' + subscription.status + ' <button class="cancel-subscription" data-subscription-id="' + subscription.id + '">Cancel</button></li>';
                        });
                        html += '</ul>';
                        $('#subscriptions').html(html);
                    } else {
                        $('#subscriptions').html('<p>' + response.data + '</p>');
                    }
                }
            });

            $(document).on('click', '.cancel-subscription', function() {
                var subscriptionId = $(this).data('subscription-id');
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    method: 'POST',
                    data: {
                        action: 'juchheim_cancel_subscription',
                        subscription_id: subscriptionId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Subscription canceled successfully.');
                            location.reload();
                        } else {
                            alert('Failed to cancel subscription: ' + response.data);
                        }
                    }
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('juchheim_subscriptions', 'juchheim_display_subscriptions');

// Fetch Subscriptions Using AJAX
function juchheim_fetch_subscriptions() {
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to view subscriptions.');
    }

    $customer_email = sanitize_email($_POST['email']);
    \Stripe\Stripe::setApiKey('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');

    try {
        $customers = \Stripe\Customer::all(['email' => $customer_email]);
        if (empty($customers->data)) {
            wp_send_json_error('No subscriptions found for this email.');
        }

        $customer = $customers->data[0];
        $subscriptions = \Stripe\Subscription::all(['customer' => $customer->id]);

        $response = [];
        foreach ($subscriptions->data as $subscription) {
            $product = \Stripe\Product::retrieve($subscription->items->data[0]->price->product);
            $response[] = [
                'id' => $subscription->id,
                'product' => $product->name,
                'status' => $subscription->status,
            ];
        }

        wp_send_json_success($response);
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}
add_action('wp_ajax_juchheim_fetch_subscriptions', 'juchheim_fetch_subscriptions');
add_action('wp_ajax_nopriv_juchheim_fetch_subscriptions', 'juchheim_fetch_subscriptions');

// Cancel Subscriptions Using AJAX
function juchheim_cancel_subscription() {
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to cancel subscriptions.');
    }

    $subscription_id = sanitize_text_field($_POST['subscription_id']);
    \Stripe\Stripe::setApiKey('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');

    try {
        $subscription = \Stripe\Subscription::retrieve($subscription_id);
        $subscription->cancel();

        // Send email notification
        $user = wp_get_current_user();
        $to = 'ernest@juchheim.online'; // Replace with your email address
        $subject = 'Subscription Cancellation Notification';
        $message = "A subscription has been canceled:\n\n";
        $message .= "User Name: " . $user->display_name . "\n";
        $message .= "User Email: " . $user->user_email . "\n";
        $message .= "Subscription ID: " . $subscription_id . "\n";
        $message .= "Product Name: " . $subscription->items->data[0]->price->product . "\n";
        $headers = array('Content-Type: text/plain; charset=UTF-8');

        wp_mail($to, $subject, $message, $headers);

        wp_send_json_success('Subscription canceled successfully.');
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}
add_action('wp_ajax_juchheim_cancel_subscription', 'juchheim_cancel_subscription');
add_action('wp_ajax_nopriv_juchheim_cancel_subscription', 'juchheim_cancel_subscription');


// Hide the Admin Bar for Subscribers
function juchheim_hide_admin_bar_for_subscribers() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        if (in_array('subscriber', (array) $user->roles)) {
            // Hide the admin bar
            add_filter('show_admin_bar', '__return_false');
        }
    }
}
add_action('after_setup_theme', 'juchheim_hide_admin_bar_for_subscribers');
?>
