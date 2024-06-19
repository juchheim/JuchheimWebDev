<?php
require __DIR__ . '/vendor/autoload.php'; // Path to the Stripe autoload file

// Verify nonce
if (!isset($_POST['stripe_nonce']) || !wp_verify_nonce($_POST['stripe_nonce'], 'stripe_nonce')) {
    error_log('Nonce verification failed');
    http_response_code(403);
    echo json_encode(['error' => 'Invalid nonce']);
    exit;
}

\Stripe\Stripe::setApiKey('sk_test_51PRj4aHrZfxkHCcnjYNK7r3Ev1e1sIlU4R3itbutVSG1fJKAzfEOehjvFZz7B9A8v5Hu0fF0Dh9sv5ZYmbrd9swh00VLTD1J2Q');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $plan = $_POST['plan'];
        $priceId = ($plan === 'monthly') ? 'price_1PTTKAHrZfxkHCcnPB3l0Cbc' : 'price_1PTToQHrZfxkHCcntMWJbMkM'; // Replace with your Stripe price IDs

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
        echo json_encode(['error' => 'Internal Server Error']);
    }
    exit;
}
?>
