<?php
// Ensure the script is being run within WordPress

echo "send-email.php loaded.";

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to test sending an email
function test_wp_mail_smtp() {
    $to = 'juchheim@gmail.com'; // Replace with your email address
    $subject = 'WP Mail SMTP Test';
    $message = 'This is a test email sent using WP Mail SMTP configured with Elastic Email.';
    $headers = array('Content-Type: text/html; charset=UTF-8');

    if (wp_mail($to, $subject, $message, $headers)) {
        echo 'Email sent successfully!';
    } else {
        echo 'Failed to send email.';
    }
}

// Hook the test function to WordPress init action
add_action('init', 'test_wp_mail_smtp');
?>
