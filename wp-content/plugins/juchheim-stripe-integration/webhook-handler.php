<?php
// Include the Composer autoload file
require_once __DIR__ . '/vendor/autoload.php';

use Stripe\Webhook;
use Stripe\Stripe;

// Set Stripe API key
Stripe::setApiKey('sk_test_51PRj4aHrZfxkHCcnjYNK7r3Ev1e1sIlU4R3itbutVSG1fJKAzfEOehjvFZz7B9A8v5Hu0fF0Dh9sv5ZYmbrd9swh00VLTD1J2Q');

add_action('rest_api_init', function() {
    register_rest_route('juchheim-stripe/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'handle_stripe_webhook',
    ));
});

function handle_stripe_webhook(WP_REST_Request $request) {
    $payload = $request->get_body();
    $sig_header = $request->get_header('stripe-signature');
    $event = null;

    error_log('Webhook received: ' . $payload);

    try {
        $event = Webhook::constructEvent(
            $payload, $sig_header, 'whsec_FvNAupwXBRlM8JCgu1nefxOeBCqU2wAo'
        );
    } catch (\UnexpectedValueException $e) {
        // Invalid payload
        error_log('Invalid payload');
        return new WP_Error('invalid_payload', 'Invalid payload', array('status' => 400));
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        error_log('Invalid signature: ' . $e->getMessage());
        return new WP_Error('invalid_signature', 'Invalid signature', array('status' => 400));
    }

    // Handle the event
    switch ($event['type']) {
        case 'checkout.session.completed':
            $session = $event['data']['object'];

            // Log the entire session object for debugging
            error_log('Checkout Session: ' . print_r($session, true));

            // Extract the customer's email and other relevant information
            $customer_email = $session['customer_details']['email'];
            $name = $session['metadata']['name'];
            $password = $session['metadata']['password'];

            // Log received data for debugging
            error_log("Received webhook: customer_email=$customer_email, name=$name");

            // Create a new WordPress user
            if (email_exists($customer_email) == false) {
                $user_id = wp_create_user($name, $password, $customer_email);

                if (is_wp_error($user_id)) {
                    error_log('User creation failed: ' . $user_id->get_error_message());
                } else {
                    // Optionally, you can set additional user meta or roles here
                    wp_update_user(array('ID' => $user_id, 'display_name' => $name));
                }
            } else {
                error_log('User already exists: ' . $customer_email);
            }

            break;
        default:
            // Unexpected event type
            error_log('Unexpected event type: ' . $event['type']);
            return new WP_Error('unexpected_event_type', 'Unexpected event type', array('status' => 400));
    }

    return new WP_REST_Response('Webhook handled', 200);
}
