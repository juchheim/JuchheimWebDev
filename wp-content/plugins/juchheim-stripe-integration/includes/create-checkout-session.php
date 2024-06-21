<?php
require plugin_dir_path(__FILE__) . '../vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51PRj4aHrZfxkHCcnjYNK7r3Ev1e1sIlU4R3itbutVSG1fJKAzfEOehjvFZz7B9A8v5Hu0fF0Dh9sv5ZYmbrd9swh00VLTD1J2Q');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['stripe_nonce']) || !wp_verify_nonce($_POST['stripe_nonce'], 'stripe_nonce')) {
        wp_send_json_error('Invalid nonce');
        exit;
    }

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);
    $plan = isset($_POST['plan']) ? sanitize_text_field($_POST['plan']) : null;
    $price = isset($_POST['price']) ? sanitize_text_field($_POST['price']) : null;

    try {
        $amount = ($plan === 'monthly') ? 2500 : ($plan === 'annually' ? 25000 : ($plan === '10-page-no-sub' ? 100000 : 150000));
        if ($price) {
            $amount = $price * 100;
        }

        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $plan ? $plan : 'Custom Price',
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $email, // Pass the email address to Stripe
            'success_url' => home_url('/payment-success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => home_url('/payment-failed'),
            'metadata' => [
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ],
        ]);

        wp_send_json(['id' => $checkout_session->id]);
    } catch (Exception $e) {
        wp_send_json(['error' => $e->getMessage()]);
    }
}
?>
