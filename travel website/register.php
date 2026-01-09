<?php

/**
 * Registration page - User registration form
 * Implements: Server-side validation, password hashing, XSS protection
 */

// Start output buffering
ob_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Include authentication functions
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: home.php');
    exit();
}

/**
 * Escape HTML output to prevent XSS
 * @param string $string The string to escape
 * @return string Escaped string
 */
function escape($string)
{
    if ($string === null) {
        return '';
    }
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

// Initialize variables
$username = $email = '';
$errors = [];
$success_msg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate password confirmation
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    // Register user
    $result = register_user($username, $email, $password);

    if ($result['success']) {
        // Auto-login after registration
        login_user($result['user_id'], $username, 'authenticated');

        // Set success message and redirect
        $_SESSION['registration_success'] = 'Registration successful! You are now logged in.';
        header('Location: home.php');
        exit();
    } else {
        $errors['general'] = $result['message'];
        // Keep username and email for form pre-filling
    }
}

// Get success message from session (if redirected)
if (isset($_SESSION['registration_success'])) {
    $success_msg = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <!-- custom CSS file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <!-- the header section starts  -->

    <section class="header">

        <a href="home.php" class="logo">Lets travel</a>

        <nav class="navbar">
            <a href="home.php">home</a>
            <a href="about.php">about</a>
            <a href="package.php">package</a>
            <a href="book.php">book</a>
            <?php if (is_logged_in()): ?>
                <a href="logout.php">logout</a>
            <?php else: ?>
                <a href="login.php">login</a>
                <a href="register.php">register</a>
            <?php endif; ?>
        </nav>

        <div id="menu-btn" class="menu-icon">☰</div>

    </section>

    <!-- header section ends -->

    <div class="heading heading-book">
        <h1>register</h1>
    </div>

    <!-- registration section starts  -->

    <section class="booking">

        <h1 class="heading-title">create an account</h1>

        <?php if ($success_msg): ?>
            <div class="success-message">
                <?php echo escape($success_msg); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errors['general'])): ?>
            <div class="error-message">
                <?php echo escape($errors['general']); ?>
            </div>
        <?php endif; ?>

        <form action="" method="post" class="book-form">

            <div class="flex">
                <div class="inputBox">
                    <label for="username">username: <span>*</span></label>
                    <input type="text" id="username" placeholder="enter your username" maxlength="20" name="username" value="<?php echo escape($username); ?>" required pattern="[a-zA-Z0-9_]+" title="Username can only contain letters, numbers, and underscores">
                    <?php if (isset($errors['username'])): ?>
                        <span class="error-text"><?php echo escape($errors['username']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="inputBox">
                    <label for="email">email: <span>*</span></label>
                    <input type="email" id="email" maxlength="50" placeholder="enter your email" name="email" value="<?php echo escape($email); ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-text"><?php echo escape($errors['email']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="inputBox">
                    <label for="password">password: <span>*</span></label>
                    <input type="password" id="password" placeholder="enter your password" name="password" minlength="6" required>
                    <?php if (isset($errors['password'])): ?>
                        <span class="error-text"><?php echo escape($errors['password']); ?></span>
                    <?php endif; ?>
                    <small style="display: block; margin-top: 0.5rem; color: #666;">Password must be at least 6 characters</small>
                </div>
                <div class="inputBox">
                    <label for="confirm_password">confirm password: <span>*</span></label>
                    <input type="password" id="confirm_password" placeholder="confirm your password" name="confirm_password" minlength="6" required>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <span class="error-text"><?php echo escape($errors['confirm_password']); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <input type="submit" value="register" class="btn" name="register">

            <p style="text-align: center; margin-top: 2rem;">
                Already have an account? <a href="login.php">Login here</a>
            </p>

        </form>

    </section>

    <!-- registration section ends -->


    <!-- footer section starts  -->

    <section class="footer">

        <div class="box-container">

            <div class="box">
                <h3>quick links</h3>
                <a href="home.php"> → home</a>
                <a href="about.php"> → about</a>
                <a href="package.php"> → package</a>
                <a href="book.php"> → book</a>
            </div>

            <div class="box">
                <h3>extra links</h3>
                <a href="#"> → ask questions</a>
                <a href="#"> → about us</a>
            </div>

            <div class="box">
                <h3>contact info</h3>
                <a href="#"> 📞 +123-456-7890 </a>
                <a href="#"> ✉ lets_travel@gmail.com </a>
                <a href="#"> 📍 prague, czech republic - 120 00 </a>
            </div>

        </div>

        <div class="credit"> created by <span>kateryna kuznetsova</span> </div>

    </section>

    <!-- footer section ends -->


    <!-- custom js file link  -->
    <script src="js/script.js"></script>

</body>

</html>
<?php
// Flush output buffer
ob_end_flush();
?>