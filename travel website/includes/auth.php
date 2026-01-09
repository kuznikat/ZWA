<?php

/**
 * Authentication Helper Functions
 * Provides user authentication, session management, and role checking
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function get_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * @return string|null Username or null if not logged in
 */
function get_username()
{
    return $_SESSION['username'] ?? null;
}

/**
 * Get current user role
 * @return string User role ('unauthenticated', 'authenticated', or 'admin')
 */
function get_user_role()
{
    if (!is_logged_in()) {
        return 'unauthenticated';
    }
    return $_SESSION['role'] ?? 'authenticated';
}

/**
 * Check if user has a specific role
 * @param string $role Role to check ('unauthenticated', 'authenticated', 'admin')
 * @return bool True if user has the role, false otherwise
 */
function has_role($role)
{
    return get_user_role() === $role;
}

/**
 * Check if user is admin
 * @return bool True if user is admin, false otherwise
 */
function is_admin()
{
    return has_role('admin');
}

/**
 * Check if user is authenticated (not unauthenticated)
 * @return bool True if user is authenticated or admin, false otherwise
 */
function is_authenticated()
{
    return is_logged_in() && !has_role('unauthenticated');
}

/**
 * Require user to be logged in
 * Redirects to login page if not logged in
 * @param string $redirect_url URL to redirect to after login (optional)
 */
function require_login($redirect_url = null)
{
    if (!is_logged_in()) {
        $redirect = $redirect_url ? '?redirect=' . urlencode($redirect_url) : '';
        header('Location: login.php' . $redirect);
        exit();
    }
}

/**
 * Require user to be admin
 * Redirects to home page if not admin
 */
function require_admin()
{
    if (!is_admin()) {
        header('Location: home.php');
        exit();
    }
}

/**
 * Login user (set session variables)
 * @param int $user_id User ID
 * @param string $username Username
 * @param string $role User role
 */
function login_user($user_id, $username, $role)
{
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
}

/**
 * Logout user (destroy session)
 */
function logout_user()
{
    $_SESSION = array();

    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    session_destroy();
}

/**
 * Register a new user
 * @param string $username Username
 * @param string $email Email address
 * @param string $password Plain text password
 * @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
 */
function register_user($username, $email, $password)
{
    require_once __DIR__ . '/db.php';

    $conn = get_db_connection();
    if ($conn === null) {
        return [
            'success' => false,
            'message' => 'Database connection failed. Please try again later.',
            'user_id' => null
        ];
    }

    // Validate input
    $errors = [];

    if (empty(trim($username))) {
        $errors[] = 'Username is required';
    } elseif (strlen(trim($username)) > 20) {
        $errors[] = 'Username must be 20 characters or less';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($username))) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }

    if (empty(trim($email))) {
        $errors[] = 'Email is required';
    } elseif (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } elseif (strlen(trim($email)) > 50) {
        $errors[] = 'Email must be 50 characters or less';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => implode('. ', $errors),
            'user_id' => null
        ];
    }

    $username = trim($username);
    $email = trim($email);

    try {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Username already exists',
                'user_id' => null
            ];
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Email already exists',
                'user_id' => null
            ];
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'authenticated')");
        $stmt->execute([$username, $email, $hashed_password]);

        $user_id = $conn->lastInsertId();

        return [
            'success' => true,
            'message' => 'Registration successful!',
            'user_id' => $user_id
        ];
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'An error occurred. Please try again later.',
            'user_id' => null
        ];
    }
}

/**
 * Authenticate user (login)
 * @param string $username_or_email Username or email
 * @param string $password Plain text password
 * @return array ['success' => bool, 'message' => string, 'user' => array|null]
 */
function authenticate_user($username_or_email, $password)
{
    require_once __DIR__ . '/db.php';

    $conn = get_db_connection();
    if ($conn === null) {
        return [
            'success' => false,
            'message' => 'Database connection failed. Please try again later.',
            'user' => null
        ];
    }

    if (empty(trim($username_or_email)) || empty($password)) {
        return [
            'success' => false,
            'message' => 'Username/email and password are required',
            'user' => null
        ];
    }

    try {
        // Try to find user by username or email
        $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
        $stmt->execute([trim($username_or_email), trim($username_or_email)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid username/email or password',
                'user' => null
            ];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Invalid username/email or password',
                'user' => null
            ];
        }

        return [
            'success' => true,
            'message' => 'Login successful!',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
    } catch (PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'An error occurred. Please try again later.',
            'user' => null
        ];
    }
}
