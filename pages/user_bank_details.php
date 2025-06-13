<?php
session_start();
require_once '../includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header('Location: ../pages/signIn.php');
    exit();
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['userId'];
    $account_holder_name = trim($_POST['account_holder_name']);
    $bank_name = $_POST['bank_name'];
    $branch_code = trim($_POST['branch_code']);
    $account_number = trim($_POST['account_number']);
    $account_type = $_POST['account_type'];
    
    // Validation
    $errors = [];
    
    if (empty($account_holder_name)) {
        $errors[] = "Account holder name is required";
    }
    
    if (empty($bank_name)) {
        $errors[] = "Bank name is required";
    }
    
    if (empty($branch_code) || !preg_match('/^\d{6}$/', $branch_code)) {
        $errors[] = "Valid 6-digit branch code is required";
    }
    
    if (empty($account_number) || !preg_match('/^\d{9,11}$/', $account_number)) {
        $errors[] = "Valid account number (9-11 digits) is required";
    }
    
    if (empty($account_type)) {
        $errors[] = "Account type is required";
    }
    
    // Check if user already has bank details
    $check_stmt = $conn->prepare("SELECT id FROM seller_bank_details WHERE userId = ?");
    $check_stmt->execute([$user_id]);
    
    if ($check_stmt->rowCount() > 0) {
        $errors[] = "Bank details already exist. Please update them instead.";
    }
    
    if (empty($errors)) {
        try {
            // Insert bank details
            $stmt = $conn->prepare("
                INSERT INTO seller_bank_details 
                (userId, account_holder_name, bank_name, branch_code, account_number, account_type) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $success = $stmt->execute([
                $user_id,
                $account_holder_name,
                $bank_name,
                $branch_code,
                $account_number, // In production, encrypt this
                $account_type
            ]);
            
            if ($success) {
                // Update user seller setup status
                $update_user = $conn->prepare("UPDATE users SET is_seller = TRUE WHERE userId = ?");
                $update_user->execute([$user_id]);
                
                $_SESSION['success_message'] = "Bank details added successfully! We'll send a verification deposit within 1-2 business days.";
                header('Location: myListing.php');
                exit();
            } else {
                $errors[] = "Failed to save bank details. Please try again.";
            }
            
        } catch (PDOException $e) {
            $errors[] = "Database error. Please try again later.";
            error_log("Bank details error: " . $e->getMessage());
        }
    }
    
    // If there are errors, store them in session and redirect back
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: user_bank_details.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<?php  
    $pageTitle = "Bank Deatils"; 
    include('../includes/head.php');  
?> 
<head>
    <link rel="stylesheet" href="../assets/css/user_bank_details.css"> 
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="text-center mb-4">
                <h2>🏦 Add Your Bank Details</h2>
                <p class="text-muted">Complete your seller setup to receive payouts</p>
            </div>

            <div class="security-note">
                <strong>🔒 Your information is secure</strong><br>
                We use bank-level encryption to protect your banking details. We'll send a small verification deposit to confirm your account.
            </div>

            <?php
                if (isset($_SESSION['form_errors'])) {
                    echo '<div class="alert alert-danger">';
                    foreach ($_SESSION['form_errors'] as $error) {
                        echo '<p>' . htmlspecialchars($error) . '</p>';
                    }
                    echo '</div>';
                    unset($_SESSION['form_errors']);
                }
            ?>

            <form id="bankDetailsForm" method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="account_holder_name" class="form-label">Account Holder Name *</label>
                        <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" required>
                        <div class="form-text">Must match your ID/Passport exactly</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="bank_name" class="form-label">Bank Name *</label>
                        <select class="form-select" id="bank_name" name="bank_name" required>
                            <option value="">Select your bank</option>
                            <option value="ABSA Bank">ABSA Bank</option>
                            <option value="Standard Bank">Standard Bank</option>
                            <option value="FNB">First National Bank (FNB)</option>
                            <option value="Nedbank">Nedbank</option>
                            <option value="Capitec Bank">Capitec Bank</option>
                            <option value="Discovery Bank">Discovery Bank</option>
                            <option value="TymeBank">TymeBank</option>
                            <option value="African Bank">African Bank</option>
                            <option value="Investec">Investec</option>
                            <option value="Bidvest Bank">Bidvest Bank</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="branch_code" class="form-label">Branch Code *</label>
                        <input type="text" class="form-control" id="branch_code" name="branch_code" maxlength="10" required>
                        <div class="form-text">6-digit branch code</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="account_type" class="form-label">Account Type *</label>
                        <select class="form-select" id="account_type" name="account_type" required>
                            <option value="">Select account type</option>
                            <option value="savings">Savings Account</option>
                            <option value="current">Current Account</option>
                            <option value="cheque">Cheque Account</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="account_number" class="form-label">Account Number *</label>
                    <input type="text" class="form-control" id="account_number" name="account_number" maxlength="20" required>
                    <div class="form-text">Enter your full account number</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_account_number" class="form-label">Confirm Account Number *</label>
                    <input type="text" class="form-control" id="confirm_account_number" name="confirm_account_number" maxlength="20" required>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="terms_agreement" name="terms_agreement" required>
                    <label class="form-check-label" for="terms_agreement">
                        I confirm that these bank details are correct and I authorize payouts to this account. I understand a verification deposit will be made.
                    </label>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        💳 Add Bank Details
                    </button>
                    <a href="seller_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>

            <div class="mt-4 text-center">
                <small class="text-muted">
                    Need help? <a href="support.php">Contact Support</a> | 
                    <a href="bank_verification_help.php">Verification Process</a>
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('bankDetailsForm').addEventListener('submit', function(e) {
            const accountNumber = document.getElementById('account_number').value;
            const confirmAccount = document.getElementById('confirm_account_number').value;
            
            if (accountNumber !== confirmAccount) {
                e.preventDefault();
                alert('Account numbers do not match. Please check and try again.');
                return false;
            }

            // Basic validation for South African account numbers
            if (accountNumber.length < 9 || accountNumber.length > 11) {
                e.preventDefault();
                alert('Please enter a valid account number (9-11 digits).');
                return false;
            }
        });

        // Auto-populate branch codes for major banks
        document.getElementById('bank_name').addEventListener('change', function() {
            const branchCodeField = document.getElementById('branch_code');
            const commonBranchCodes = {
                'FNB': '250655',
                'Standard Bank': '051001',
                'ABSA Bank': '632005',
                'Nedbank': '198765',
                'Capitec Bank': '470010'
            };
            
            if (commonBranchCodes[this.value]) {
                branchCodeField.value = commonBranchCodes[this.value];
                branchCodeField.setAttribute('placeholder', 'Universal branch code auto-filled');
            } else {
                branchCodeField.value = '';
                branchCodeField.setAttribute('placeholder', 'Enter your branch code');
            }
        });
    </script>
</body>
</html>