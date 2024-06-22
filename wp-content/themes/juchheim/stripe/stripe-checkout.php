<?php
require_once('../../../../wp-load.php'); // Adjust the path as needed to include WordPress functions

// Include the Stripe PHP library
require_once __DIR__ . '/vendor/autoload.php';

// Set your secret key. Remember to switch to your live secret key in production.
\Stripe\Stripe::setApiKey('sk_test_51PRj4aHrZfxkHCcnjYNK7r3Ev1e1sIlU4R3itbutVSG1fJKAzfEOehjvFZz7B9A8v5Hu0fF0Dh9sv5ZYmbrd9swh00VLTD1J2Q');

// Sanitize and validate the POST parameters
$name = sanitize_text_field($_POST['name']);
$email = sanitize_email($_POST['email']);
$password = sanitize_text_field($_POST['password']);
$plan = sanitize_text_field($_POST['plan']);

// Create a new Stripe customer
try {
    $customer = \Stripe\Customer::create([
        'email' => $email,
        'name' => $name,
        'metadata' => [
            'username' => $name,
            'password' => $password,
        ],
    ]);

    // Create a Checkout Session
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'customer' => $customer->id,
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $plan === 'monthly' ? 'Monthly Plan' : 'Annual Plan',
                ],
                'unit_amount' => $plan === 'monthly' ? 2500 : 25000,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'subscription',
        'success_url' => 'https://juchheim.online/success?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'https://juchheim.online/cancel',
    ]);

    // Return the session ID to the frontend
    echo json_encode(['id' => $checkout_session->id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
