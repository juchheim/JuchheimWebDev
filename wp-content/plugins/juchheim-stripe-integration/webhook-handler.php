<?php
// Include the Composer autoload file to load necessary dependencies
require_once __DIR__ . '/vendor/autoload.php';

use Stripe\Webhook;
use Stripe\Stripe;

// Set the Stripe API key (replace with your actual production API key)
Stripe::setApiKey('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'); // Make sure to use your live secret key

// Register the webhook handler route with the WordPress REST API
add_action('rest_api_init', function() {
    register_rest_route('juchheim-stripe/v1', '/webhook', array(
        'methods' => 'POST', // This endpoint accepts POST requests
        'callback' => 'handle_stripe_webhook', // Function to handle the webhook
    ));
});

// Function to handle the Stripe webhook
function handle_stripe_webhook(WP_REST_Request $request) {
    // Retrieve the raw body of the request
    $payload = $request->get_body();
    // Retrieve the Stripe signature header to verify the request
    $sig_header = $request->get_header('stripe-signature');
    $event = null;

    // Log the received webhook payload for debugging purposes
    error_log('Webhook received: ' . $payload);

    try {
        // Construct the event using the payload and signature
        $event = Webhook::constructEvent(
            $payload, $sig_header, 'whsec_9hagU5Hzd6BGr6oVxGp7mkybAZn1Ju3Y' // Replace with your actual live webhook secret
        );
    } catch (\UnexpectedValueException $e) {
        // Log an error if the payload is invalid
        error_log('Invalid payload');
        return new WP_Error('invalid_payload', 'Invalid payload', array('status' => 400));
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        // Log an error if the signature is invalid
        error_log('Invalid signature: ' . $e->getMessage());
        return new WP_Error('invalid_signature', 'Invalid signature', array('status' => 400));
    }

    // Handle different types of events sent by Stripe
    switch ($event['type']) {
        case 'checkout.session.completed': // Handle checkout session completion event
            $session = $event['data']['object']; // Get the session object from the event

            // Log the entire session object for debugging purposes
            error_log('Checkout Session: ' . print_r($session, true));

            // Extract the customer's email and other relevant information from the session
            $customer_email = $session['customer_details']['email'];
            $name = $session['metadata']['name'];
            $password = $session['metadata']['password'];
            $product_name = isset($session['display_items'][0]['custom']['name']) ? $session['display_items'][0]['custom']['name'] : 'Custom Payment';

            // Log the received data for debugging purposes
            error_log("Received webhook: customer_email=$customer_email, name=$name, password=$password, product=$product_name");

            // Check if the email already exists in WordPress
            if (email_exists($customer_email) == false) {
                // Create a new WordPress user if the email does not exist
                $user_id = wp_create_user($name, $password, $customer_email);

                if (is_wp_error($user_id)) {
                    // Log an error if user creation fails
                    error_log('User creation failed: ' . $user_id->get_error_message());
                } else {
                    // Optionally, you can set additional user meta or roles here
                    wp_update_user(array('ID' => $user_id, 'display_name' => $name));
                    error_log("User created successfully: user_id=$user_id");

                    // Send an email notification using wp_mail()
                    $to = 'juchheim@gmail.com';
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
                // Log a message if the user already exists
                error_log('User already exists: ' . $customer_email);
            }

            break;
        default:
            // Log an error for unexpected event types
            error_log('Unexpected event type: ' . $event['type']);
            return new WP_Error('unexpected_event_type', 'Unexpected event type', array('status' => 400));
    }

    // Return a success response to Stripe
    return new WP_REST_Response('Webhook handled', 200);
}
?>
