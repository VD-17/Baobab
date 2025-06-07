<?php
    session_start();
    require_once("../includes/db_connection.php");

    if (!isset($_SESSION['email'])) {
        die("No email provided.");
    }

    $email = $_SESSION['email'];

    $token = bin2hex(random_bytes(16));
    $_SESSION['reset_token'] = $token;
    $token_hash = hash("sha256", $token);
    $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

    try {
        $stmt = $conn->prepare("UPDATE users 
                            SET reset_token_hash = :token_hash,
                                reset_token_expires_at = :expiry
                            WHERE email = :email");
        $stmt->execute([
            'token_hash' => $token_hash,
            'expiry' => $expiry,
            'email' => $email
        ]);

        if ($stmt->rowCount() > 0) {
            $mail = require_once("../api/authentication/mailer.php");

            $mail->setFrom("noreply@example.com", "Baobab");
            $mail->addAddress($email);
            $mail->Subject = "Password Reset";
            $mail->Body = <<<END
    Click <a href="http://example.com/pages/reset-password.php?token=$token">here</a> to reset your password.
    This link will expire in 30 minutes.
    END;

            try {
                $mail->send();
                // echo "Message sent, please check your inbox.";
                header("Location: ../pages/reset-password.php");
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
            }
        } else {
            echo "No user found with that email address.";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }

    // Clear session email
    unset($_SESSION['email']);
?>