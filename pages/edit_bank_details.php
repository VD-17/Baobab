<?php
session_start();
require_once '../includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header('Location: ../pages/signIn.php');
    exit();
} 

$user_id = $_SESSION['userId'];

// Get existing bank details
$stmt = $conn->prepare("SELECT * FROM seller_bank_details WHERE userId = ?");
$stmt->execute([$user_id]);
$bank_details = $stmt->fetch(PDO::FETCH_ASSOC);

// If no bank details exist, redirect to add page
if (!$bank_details) {
    $_SESSION['info_message'] = "Please add your bank details first.";
    header('Location: user_bank_details.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    if (empty($errors)) {
        try {
            // Update bank details
            $update_stmt = $conn->prepare("
                UPDATE seller_bank_details 
                SET account_holder_name = ?, bank_name = ?, branch_code = ?, 
                    account_number = ?, account_type = ?, updated_at = NOW(),
                    is_verified = FALSE
                WHERE userId = ?
            ");
            
            $success = $update_stmt->execute([
                $account_holder_name,
                $bank_name,
                $branch_code,
                $account_number, // In production, encrypt this
                $account_type,
                $user_id
            ]);
            
            if ($success) {
                $_SESSION['success_message'] = "Bank details updated successfully! We'll send a new verification deposit within 1-2 business days due to account changes.";
                header('Location: userDashboard.php');
                exit();
            } else {
                $errors[] = "Failed to update bank details. Please try again.";
            }
            
        } catch (PDOException $e) {
            $errors[] = "Database error. Please try again later.";
            error_log("Bank details update error: " . $e->getMessage());
        }
    }
    
    // If there are errors, store them in session
    $_SESSION['form_errors'] = $errors;
}

// Use form data if available, otherwise use existing bank details
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : $bank_details;
?>

<!DOCTYPE html>
<html lang="en">
<?php  
    $pageTitle = "Edit Bank Details"; 
    include('../includes/head.php');  
?> 
<head>
    <link rel="stylesheet" href="../assets/css/user_bank_details.css"> 
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="text-center mb-4">
                <h2>✏️ Edit Your Bank Details</h2>
                <p class="text-muted">Update your banking information for payouts</p>
            </div>

            <div class="alert alert-info">
                <strong>ℹ️ Important Notice:</strong><br>
                Updating your bank details will require re-verification. We'll send a new verification deposit to your updated account within 1-2 business days.
            </div>

            <div class="security-note">
                <strong>🔒 Your information is secure</strong><br>
                We use bank-level encryption to protect your banking details.
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

                if (isset($_SESSION['info_message'])) {
                    echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['info_message']) . '</div>';
                    unset($_SESSION['info_message']);
                }
            ?>

            <form id="editBankDetailsForm" method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="account_holder_name" class="form-label">Account Holder Name *</label>
                        <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" 
                               value="<?php echo htmlspecialchars($form_data['account_holder_name']); ?>" required>
                        <div class="form-text">Must match your ID/Passport exactly</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="bank_name" class="form-label">Bank Name *</label>
                        <select class="form-select" id="bank_name" name="bank_name" required>
                            <option value="">Select your bank</option>
                            <option value="ABSA Bank" <?php echo ($form_data['bank_name'] == 'ABSA Bank') ? 'selected' : ''; ?>>ABSA Bank</option>
                            <option value="Standard Bank" <?php echo ($form_data['bank_name'] == 'Standard Bank') ? 'selected' : ''; ?>>Standard Bank</option>
                            <option value="FNB" <?php echo ($form_data['bank_name'] == 'FNB') ? 'selected' : ''; ?>>First National Bank (FNB)</option>
                            <option value="Nedbank" <?php echo ($form_data['bank_name'] == 'Nedbank') ? 'selected' : ''; ?>>Nedbank</option>
                            <option value="Capitec Bank" <?php echo ($form_data['bank_name'] == 'Capitec Bank') ? 'selected' : ''; ?>>Capitec Bank</option>
                            <option value="Discovery Bank" <?php echo ($form_data['bank_name'] == 'Discovery Bank') ? 'selected' : ''; ?>>Discovery Bank</option>
                            <option value="TymeBank" <?php echo ($form_data['bank_name'] == 'TymeBank') ? 'selected' : ''; ?>>TymeBank</option>
                            <option value="African Bank" <?php echo ($form_data['bank_name'] == 'African Bank') ? 'selected' : ''; ?>>African Bank</option>
                            <option value="Investec" <?php echo ($form_data['bank_name'] == 'Investec') ? 'selected' : ''; ?>>Investec</option>
                            <option value="Bidvest Bank" <?php echo ($form_data['bank_name'] == 'Bidvest Bank') ? 'selected' : ''; ?>>Bidvest Bank</option>
                            <option value="Other" <?php echo ($form_data['bank_name'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="branch_code" class="form-label">Branch Code *</label>
                        <input type="text" class="form-control" id="branch_code" name="branch_code" maxlength="10" 
                               value="<?php echo htmlspecialchars($form_data['branch_code']); ?>" required>
                        <div class="form-text">6-digit branch code</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="account_type" class="form-label">Account Type *</label>
                        <select class="form-select" id="account_type" name="account_type" required>
                            <option value="">Select account type</option>
                            <option value="savings" <?php echo ($form_data['account_type'] == 'savings') ? 'selected' : ''; ?>>Savings Account</option>
                            <option value="current" <?php echo ($form_data['account_type'] == 'current') ? 'selected' : ''; ?>>Current Account</option>
                            <option value="cheque" <?php echo ($form_data['account_type'] == 'cheque') ? 'selected' : ''; ?>>Cheque Account</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="account_number" class="form-label">Account Number *</label>
                    <input type="text" class="form-control" id="account_number" name="account_number" maxlength="20" 
                           value="<?php echo htmlspecialchars($form_data['account_number']); ?>" required>
                    <div class="form-text">Enter your full account number</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_account_number" class="form-label">Confirm Account Number *</label>
                    <input type="text" class="form-control" id="confirm_account_number" name="confirm_account_number" maxlength="20" required>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="update_agreement" name="update_agreement" required>
                    <label class="form-check-label" for="update_agreement">
                        I confirm that these updated bank details are correct and I authorize payouts to this account. I understand a new verification deposit will be made.
                    </label>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        💾 Update Bank Details
                    </button>
                    <a href="userDashboard.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>

            <!-- <div class="mt-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Current Verification Status</h6>
                        <p class="card-text">
                            Status: <span class="badge <?php echo $bank_details['is_verified'] ? 'bg-success' : 'bg-warning'; ?>">
                                <?php echo $bank_details['is_verified'] ? 'Verified' : 'Pending Verification'; ?>
                            </span>
                        </p>
                        <?php if ($bank_details['verified_at']): ?>
                            <small class="text-muted">Verified on: <?php echo date('M d, Y', strtotime($bank_details['verified_at'])); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div> -->

            <!-- <div class="mt-4 text-center">
                <small class="text-muted">
                    Need help? <a href="support.php">Contact Support</a> | 
                    <a href="bank_verification_help.php">Verification Process</a>
                </small>
            </div> -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('editBankDetailsForm').addEventListener('submit', function(e) {
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

            // Confirmation for updating bank details
            if (!confirm('Are you sure you want to update your bank details? This will require re-verification of your account.')) {
                e.preventDefault();
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
    
    <?php 
    // Clear form data after displaying
    unset($_SESSION['form_data']);
    ?>
</body>
</html>