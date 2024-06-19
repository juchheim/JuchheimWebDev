<?php
require __DIR__ . '/vendor/autoload.php'; // Path to the Stripe autoload file

\Stripe\Stripe::setApiKey('sk_test_51PRj4aHrZfxkHCcnjYNK7r3Ev1e1sIlU4R3itbutVSG1fJKAzfEOehjvFZz7B9A8v5Hu0fF0Dh9sv5ZYmbrd9swh00VLTD1J2Q');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan = $_POST['plan'];
    $priceId = ($plan === 'monthly') ? 'price_1A2B3C4D5E' : 'price_6F7G8H9I0J'; // Replace with your Stripe price IDs

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
    exit;
}
?>
