<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/wp-content/plugins/juchheim-stripe-plugin/vendor/PHPMailer/src/Exception.php';
require __DIR__ . '/wp-content/plugins/juchheim-stripe-plugin/vendor/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/wp-content/plugins/juchheim-stripe-plugin/vendor/PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = 0;                                     // Disable verbose debug output
    $mail->isSMTP();                                          // Set mailer to use SMTP
    $mail->Host       = 'smtp.elasticemail.com';              // Specify main and backup SMTP servers
    $mail->SMTPAuth   = true;                                 // Enable SMTP authentication
    $mail->Username   = '0FAF72196698DB992DF931485745156E0C1F0ABBE9CE2954ADA15695442D686901A3B15938503EDF6C18F4112CDCCA23'; // SMTP username
    $mail->Password   = '0FAF72196698DB992DF931485745156E0C1F0ABBE9CE2954ADA15695442D686901A3B15938503EDF6C18F4112CDCCA23'; // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;          // Enable SSL encryption
    $mail->Port       = 465;                                  // TCP port to connect to

    //Recipients
    $mail->setFrom('juchheim.spotify@gmail.com', 'Mailer');
    $mail->addAddress('juchheim@gmail.com', 'Recipient Name');     // Add a recipient
    $mail->addReplyTo('juchheim.spotify@gmail.com', 'Information');

    // Content
    $mail->isHTML(true);                                      // Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
