<?php

/**
 * Login page - User authentication
 * Implements: Password verification, session management, XSS protection
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
    $redirect = $_GET['redirect'] ?? 'home.php';
    header('Location: ' . $redirect);
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
$username_or_email = '';
$errors = [];
$success_msg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username_or_email = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Authenticate user
    $result = authenticate_user($username_or_email, $password);

    if ($result['success']) {
        // Login user (set session)
        login_user($result['user']['id'], $result['user']['username'], $result['user']['role']);

        // Redirect to requested page or home
        $redirect = $_GET['redirect'] ?? 'home.php';
        header('Location: ' . $redirect);
        exit();
    } else {
        $errors['general'] = $result['message'];
        // Keep username/email for form pre-filling
    }
}

// Get success message from session (if redirected)
if (isset($_SESSION['login_success'])) {
    $success_msg = $_SESSION['login_success'];
    unset($_SESSION['login_success']);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

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
        <h1>login</h1>
    </div>

    <!-- login section starts  -->

    <section class="booking">

        <h1 class="heading-title">login to your account</h1>

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
                    <label for="username_or_email">username or email: <span>*</span></label>
                    <input type="text" id="username_or_email" placeholder="enter your username or email" name="username_or_email" value="<?php echo escape($username_or_email); ?>" required>
                    <?php if (isset($errors['username_or_email'])): ?>
                        <span class="error-text"><?php echo escape($errors['username_or_email']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="inputBox">
                    <label for="password">password: <span>*</span></label>
                    <input type="password" id="password" placeholder="enter your password" name="password" required>
                    <?php if (isset($errors['password'])): ?>
                        <span class="error-text"><?php echo escape($errors['password']); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <input type="submit" value="login" class="btn" name="login">

            <p style="text-align: center; margin-top: 2rem;">
                Don't have an account? <a href="register.php">Register here</a>
            </p>

        </form>

    </section>

    <!-- login section ends -->


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