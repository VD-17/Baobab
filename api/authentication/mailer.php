<?php 
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    require __DIR__ . "/../../vendor/autoload.php";

    $mail = new PHPMailer(true);

    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;

    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->SMTPAuth = true;

    $mail->Host = 'smtp-relay.brevo.com';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->Username = '8db71a001@smtp-brevo.com';
    $mail->Password = 'kAry8axGqKQX7BMb';

    $mail->isHtml(true);
    // $mail->setFrom("noreply@example.com", "Baobab");
    // $mail->addAddress("vidhimaisuria1709@gmail.com");
    // $mail->Subject = "Test Email";
    // $mail->Body = "This is a test email sent from PHPMailer.";

    return $mail;
?>  