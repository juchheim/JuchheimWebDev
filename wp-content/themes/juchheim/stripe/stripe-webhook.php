<?php
require __DIR__ . '/vendor/autoload.php'; // Path to the Stripe autoload file

\Stripe\Stripe::setApiKey('sk_test_51PRj4aHrZfxkHCcnjYNK7r3Ev1e1sIlU4R3itbutVSG1fJKAzfEOehjvFZz7B9A8v5Hu0fF0Dh9sv5ZYmbrd9swh00VLTD1J2Q');

$endpoint_secret = 'whsec_Qpsf9ciEcgKgXYTJBC0Na98G9rEFoYjY';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch (\UnexpectedValueException $e) {
    http_response_code(400);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    exit();
}

if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;

    $name = $session->metadata->name;
    $email = $session->metadata->email;
    $password = $session->metadata->password;

    if (email_exists($email)) {
        // User already exists
    } else {
        // Create new user
        wp_create_user($email, $password, $email);
    }
}

http_response_code(200);
?>
