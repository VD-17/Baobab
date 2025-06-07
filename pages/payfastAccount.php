<?php 
    session_start();
    require_once '../includes/db_connection.php';

    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Insert new credentials
        $stmt = $conn->prepare("
            INSERT INTO user_payfast_credentials 
            (userId, merchant_id, merchant_key, is_sandbox, is_active, created_at) 
            VALUES (?, ?, ?, ?, TRUE, NOW())
        ");
        $stmt->execute([$userId, $merchantId, $merchantKey, $isSandbox]);
        
        $conn->commit();
        return true;
        
    } catch (PDOException $e) {
        $conn->rollback();
        error_log("Error saving PayFast credentials: " . $e->getMessage());
        return false;
    }

?>

<!DOCTYPE html>
<html lang="en">
<?php
    $pageTitle = "My Sales";
    include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body>
    <h2>Set Up a PayFast Sandbox Account for Payment</h2>
    <h3>To make payments on our platform, please create a free PayFast sandbox account. This allows you to receive payments. Follow these steps:</h3>
    <ol>
        <li>Visit <a href="sandbox.payfast.co.za" target="_blank">sandbox.payfast.co.za</a></li>
        <li>Sign up with your email and a password.</li>
        <li>Copy your Sandbox Merchant ID from the dashboard and enter it in your seller profile below.</li>
    </ol>
    <form action="">
        <input type="text" name="merchantID" id="merchantId" placeholder="Merchant ID">
        <input type="text" name="merchantKey" id="merchantKey" placeholder="Merchant Key">
    </form>
    <h6><a href="">Learn More</a></h6>
</body>
</html>