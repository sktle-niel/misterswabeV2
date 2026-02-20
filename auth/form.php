<?php
session_start();
include '../back-end/create/createAccount.php';
include '../back-end/read/fetchLogin.php';

$current_view = isset($_GET['view']) ? $_GET['view'] : 'initial';

// Store email in session when user enters it
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['temp_email'])) {
    $_SESSION['temp_email'] = $_POST['temp_email'];
    header('Location: ?view=create');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_account'])) {
    $email = $_POST['create_email'];
    $password = $_POST['create_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type']; // Get user type from form
    
    if ($password !== $confirm_password) {
        $_SESSION['create_error'] = 'Passwords do not match';
        header('Location: ?view=create');
        exit();
    } else {
        $result = createAccount($email, $password, $user_type);
        if ($result['success']) {
            $_SESSION['create_success'] = $result['message'];
            unset($_SESSION['temp_email']); // Clear temp email
            header('Location: ?view=initial');
            exit();
        } else {
            $_SESSION['create_error'] = $result['message'];
            header('Location: ?view=create');
            exit();
        }
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $result = handleLogin($email, $password);
    if ($result['success']) {
        // Redirect based on user type
        if ($_SESSION['user_type'] == 'administrator') {
            header('Location: ../public/administrator/main.php');
        } elseif ($_SESSION['user_type'] == 'staff') {
            header('Location: ../public/staff/main.php?page=dashboard');
        } else {
            header('Location: ../public/customer/main.php');
        }
        exit();
    } else {
        $_SESSION['login_error'] = $result['message'];
        header('Location: ?view=login');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mr. Swabe Apparel</title>
    <link rel="stylesheet" href="../src/css/forAuth.css">
    <style>
        .input-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
            cursor: pointer;
        }
        
        .input-group select:focus {
            outline: none;
            border-color: #333;
        }
    </style>
</head>
<body>
    <?php include 'status/success.php'; ?>
    <?php include 'status/invalid.php'; ?>
    <div class="login-container">
        <div class="logo">
            <h1>Mr. Swabe Apparel</h1>
            <p>& COLLECTIONS</p>
        </div>
        <div class="login-header">
            <h2 id="headerTitle"><?php echo $current_view == 'login' ? 'Login' : ($current_view == 'create' ? 'Create Account' : 'Sign in'); ?></h2>
            <p id="headerSubtitle"><?php echo $current_view == 'login' ? 'Enter username and password' : ($current_view == 'create' ? 'Set up your account' : 'Sign in or <a href="#">create an account</a>'); ?></p>
        </div>
        
        <!-- Initial View -->
        <div id="initialView" <?php echo $current_view != 'initial' ? 'class="hidden"' : ''; ?>>
            <button class="btn-shop" onclick="showLoginForm()">Continue with Login</button>
            <div class="divider">
                <span>or</span>
            </div>
            <form method="post">
                <div class="input-group">
                    <input type="email" name="temp_email" id="emailOnly" placeholder="Email" required>
                </div>
                <button type="submit" class="btn-continue">Continue</button>
            </form>
        </div>
        
        <!-- Login Form (Hidden Initially) -->
        <div id="loginFormView" <?php echo $current_view == 'login' ? 'class=""' : 'class="hidden"'; ?>>
            <form method="post">
                <div class="input-group">
                    <input type="email" name="email" id="loginEmail" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" id="loginPassword" placeholder="Password" required>
                </div>
                <div class="forgot-password">
                    <a onclick="showForgotPassword()">Forgot password?</a>
                </div>
                <button type="submit" name="login" class="btn-continue">Sign In</button>
                <button type="button" class="btn-back" onclick="showInitialView()">Back</button>
            </form>
        </div>
        
        <!-- Forgot Password View (Hidden Initially) -->
        <div id="forgotPasswordView" class="hidden">
            <div class="info-text">
                Enter your email address and we'll send you a link to reset your password.
            </div>
            <form onsubmit="handleForgotPassword(event)">
                <div class="input-group">
                    <input type="email" id="forgotEmail" placeholder="Email" required>
                </div>
                <button type="submit" class="btn-continue">Send Reset Link</button>
                <button type="button" class="btn-back" onclick="backToLogin()">Back to Login</button>
            </form>
        </div>
        
        <!-- Create Password View (Hidden Initially) -->
        <div id="createPasswordView" <?php echo $current_view == 'create' ? 'class=""' : 'class="hidden"'; ?>>
            <div class="info-text">
                Create a strong password for your account.
            </div>
            <form method="post">
                <div class="input-group">
                    <input type="email" name="create_email" placeholder="Email" value="<?php echo isset($_SESSION['temp_email']) ? htmlspecialchars($_SESSION['temp_email']) : ''; ?>" readonly style="background-color: #f5f5f5;">
                </div>
                
                <div class="input-group">
                    <select id="userType" name="user_type" required>
                        <option value="">Select Account Type</option>
                        <option value="staff">Staff</option>
                        <option value="administrator">Administrator</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <input type="password" id="createPassword" name="create_password" placeholder="Password" required>
                    <small id="passwordHelp" class="form-text">Password must be at least 8 characters with uppercase and lowercase letters.</small>
                </div>
                <div class="input-group">
                    <input type="password" id="confirmPassword" name="confirm_password" placeholder="Confirm Password" required>
                    <small id="confirmHelp" class="form-text text-danger" style="display: none;">Passwords do not match.</small>
                </div>
                <button type="submit" name="create_account" class="btn-continue">Create Account</button>
                <button type="button" class="btn-back" onclick="showInitialView()">Back</button>
            </form>
        </div>
    </div>

    <script src="../src/js/form.js"></script>
    <script>
        <?php if (isset($_SESSION['create_success'])) { ?>
            document.addEventListener('DOMContentLoaded', function() {
                const successMessage = document.getElementById('successMessage');
                successMessage.style.display = 'block';
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 3000);
            });
        <?php unset($_SESSION['create_success']); } ?>

        <?php if (isset($_SESSION['create_error'])) { ?>
            document.addEventListener('DOMContentLoaded', function() {
                const invalidMessage = document.getElementById('invalidMessage');
                const invalidText = document.querySelector('.invalid-text');
                invalidText.textContent = '<?php echo addslashes($_SESSION['create_error']); ?>';
                invalidMessage.style.display = 'block';
                setTimeout(() => {
                    invalidMessage.style.display = 'none';
                }, 3000);
            });
        <?php unset($_SESSION['create_error']); } ?>

        <?php if (isset($_SESSION['login_error'])) { ?>
            document.addEventListener('DOMContentLoaded', function() {
                const invalidMessage = document.getElementById('invalidMessage');
                const invalidText = document.querySelector('.invalid-text');
                invalidText.textContent = '<?php echo addslashes($_SESSION['login_error']); ?>';
                invalidMessage.style.display = 'block';
                setTimeout(() => {
                    invalidMessage.style.display = 'none';
                }, 3000);
            });
        <?php unset($_SESSION['login_error']); } ?>
    </script>
</body>
</html>