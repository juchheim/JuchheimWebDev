<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require __DIR__ . '/vendor/autoload.php'; // Path to the Stripe autoload file

// Debugging: Log incoming POST data
error_log('Received POST data: ' . print_r($_POST, true));

// Verify nonce
if (!isset($_POST['stripe_nonce']) || !wp_verify_nonce($_POST['stripe_nonce'], 'stripe_nonce')) {
    error_log('Nonce verification failed');
    http_response_code(403);
    echo json_encode(['error' => 'Invalid nonce']);
    exit;
}

\Stripe\Stripe::setApiKey('sk_test_51PRj4aHrZfxkHCcnjYNK7r3Ev1e1sIlU4R3itbutVSG1fJKAzfEOehjvFZz7B9A8v5Hu0fF0Dh9sv5ZYmbrd9swh00VLTD1J2Q');
error_log('Stripe API Key set');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $plan = $_POST['plan'];
        $priceId = ($plan === 'monthly') ? 'price_1PTTKAHrZfxkHCcnPB3l0Cbc' : 'price_1PTToQHrZfxkHCcntMWJbMkM'; // Replace with your Stripe price IDs

        error_log('Price ID: ' . $priceId);

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => 'https://wordpress-1260594-4650212.cloudwaysapps.com/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'https://wordpress-1260594-4650212.cloudwaysapps.com/cancel',
            'metadata' => [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
            ],
        ]);

        echo json_encode(['id' => $session->id]);
    } catch (Exception $e) {
        error_log('Error creating Stripe Checkout session: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
    }
    exit;
} else {
    error_log('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}
?>