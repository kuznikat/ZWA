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
 * Get current user avatar
 * @return string|null Avatar path or null if not logged in or no avatar
 */
function get_user_avatar()
{
    return $_SESSION['avatar'] ?? null;
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
 * @param string|null $avatar Avatar path (optional)
 */
function login_user($user_id, $username, $role, $avatar = null)
{
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
    if ($avatar !== null) {
        $_SESSION['avatar'] = $avatar;
    }
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
 * Get user data from database
 * @param int $user_id User ID
 * @return array|null User data or null if not found
 */
function get_user_data($user_id)
{
    require_once __DIR__ . '/db.php';

    $conn = get_db_connection();
    if ($conn === null) {
        return null;
    }

    try {
        $stmt = $conn->prepare("SELECT id, username, email, avatar, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user : null;
    } catch (PDOException $e) {
        error_log("Get user data error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get remaining spots for a tour
 * @param int $tour_id Tour ID
 * @return array ['total_capacity' => int, 'booked' => int, 'remaining' => int, 'available' => bool]
 */
function get_tour_availability($tour_id)
{
    require_once __DIR__ . '/db.php';

    $conn = get_db_connection();
    if ($conn === null) {
        return null;
    }

    try {
        // Get tour capacity
        $stmt = $conn->prepare("SELECT capacity FROM tours WHERE id = ?");
        $stmt->execute([$tour_id]);
        $tour = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tour) {
            return null;
        }

        $total_capacity = (int)($tour['capacity'] ?? 0);

        // Count booked guests for this tour
        $stmt = $conn->prepare("SELECT SUM(guests) as total_guests FROM bookings WHERE tour_id = ?");
        $stmt->execute([$tour_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $booked = (int)($result['total_guests'] ?? 0);

        $remaining = max(0, $total_capacity - $booked);
        $available = $remaining > 0;

        return [
            'total_capacity' => $total_capacity,
            'booked' => $booked,
            'remaining' => $remaining,
            'available' => $available
        ];
    } catch (PDOException $e) {
        error_log("Get tour availability error: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if tour has enough capacity for booking
 * @param int $tour_id Tour ID
 * @param int $requested_guests Number of guests requested
 * @return array ['available' => bool, 'message' => string, 'remaining' => int]
 */
function check_tour_capacity($tour_id, $requested_guests)
{
    $availability = get_tour_availability($tour_id);

    if ($availability === null) {
        return [
            'available' => false,
            'message' => 'Tour not found',
            'remaining' => 0
        ];
    }

    if (!$availability['available']) {
        return [
            'available' => false,
            'message' => 'This tour is fully booked',
            'remaining' => 0
        ];
    }

    if ($requested_guests > $availability['remaining']) {
        return [
            'available' => false,
            'message' => "Only {$availability['remaining']} spot(s) remaining",
            'remaining' => $availability['remaining']
        ];
    }

    return [
        'available' => true,
        'message' => '',
        'remaining' => $availability['remaining']
    ];
}

/**
 * Upload avatar from base64 data URL (from canvas)
 * @param string $data_url Base64 data URL (e.g., "data:image/jpeg;base64,...")
 * @param string $upload_dir Directory to upload to
 * @return array ['success' => bool, 'message' => string, 'filename' => string|null]
 */
function upload_avatar_from_data($data_url, $upload_dir = 'uploads/avatars/')
{
    // Validate data URL format
    if (!preg_match('/^data:image\/(jpeg|jpg|png|gif|webp);base64,/', $data_url)) {
        return [
            'success' => false,
            'message' => 'Invalid image data format.',
            'filename' => null
        ];
    }

    // Extract image data and type
    list($type, $data) = explode(';', $data_url);
    list(, $data) = explode(',', $data);
    $extension = str_replace('data:image/', '', $type);

    // Normalize extension
    if ($extension === 'jpeg') {
        $extension = 'jpg';
    }

    // Decode base64 data
    $image_data = @base64_decode($data, true);
    if ($image_data === false) {
        return [
            'success' => false,
            'message' => 'Failed to decode image data.',
            'filename' => null
        ];
    }

    // Validate file size (max 2MB)
    $max_size = 2 * 1024 * 1024; // 2MB in bytes
    if (strlen($image_data) > $max_size) {
        return [
            'success' => false,
            'message' => 'Image data too large. Maximum size is 2MB.',
            'filename' => null
        ];
    }

    // Create upload directory if it doesn't exist
    $base_dir = dirname(__DIR__);
    $full_upload_dir = $base_dir . '/' . trim($upload_dir, '/');
    $full_upload_dir = rtrim(str_replace('\\', '/', $full_upload_dir), '/') . '/';

    $resolved_base = realpath($base_dir);
    if ($resolved_base !== false) {
        $full_upload_dir = $resolved_base . '/' . trim($upload_dir, '/') . '/';
    }

    if (!file_exists($full_upload_dir)) {
        if (!@mkdir($full_upload_dir, 0775, true)) {
            $error_msg = "Failed to create upload directory: {$full_upload_dir}";
            error_log($error_msg);
            return [
                'success' => false,
                'message' => 'Failed to create upload directory. Please check server permissions.',
                'filename' => null
            ];
        }
        $parent_dir = dirname($full_upload_dir);
        if (file_exists($parent_dir) && !is_writable($parent_dir)) {
            @chmod($parent_dir, 0775);
        }
    }

    // Check if directory is writable
    if (!is_writable($full_upload_dir)) {
        @chmod($full_upload_dir, 0775);
        $parent_dir = dirname($full_upload_dir);
        if (file_exists($parent_dir)) {
            @chmod($parent_dir, 0775);
        }

        if (!is_writable($full_upload_dir)) {
            $absolute_path = realpath($full_upload_dir) ?: $full_upload_dir;
            $error_msg = "Upload directory is not writable: {$absolute_path}";
            error_log($error_msg);
            return [
                'success' => false,
                'message' => 'Upload directory is not writable. Please check server permissions.',
                'filename' => null
            ];
        }
    }

    // Generate unique filename
    $filename = uniqid('avatar_', true) . '.' . $extension;
    $filepath = $full_upload_dir . $filename;

    // Save image data to file
    if (@file_put_contents($filepath, $image_data) === false) {
        $error_msg = "Failed to save image data to: {$filepath}";
        error_log($error_msg);
        return [
            'success' => false,
            'message' => 'Failed to save image file.',
            'filename' => null
        ];
    }

    // Verify the saved file is a valid image
    $image_info = @getimagesize($filepath);
    if ($image_info === false) {
        @unlink($filepath);
        return [
            'success' => false,
            'message' => 'Invalid image file.',
            'filename' => null
        ];
    }

    return [
        'success' => true,
        'message' => 'Avatar uploaded successfully',
        'filename' => $upload_dir . $filename
    ];
}

/**
 * Validate and upload avatar image
 * @param array $file $_FILES array element
 * @param string $upload_dir Directory to upload to
 * @return array ['success' => bool, 'message' => string, 'filename' => string|null]
 */
function upload_avatar($file, $upload_dir = 'uploads/avatars/')
{
    // Check if file was uploaded
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [
            'success' => true,
            'message' => '',
            'filename' => null
        ];
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini (max 2MB)',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by PHP extension'
        ];

        $error_code = $file['error'];
        $error_message = $error_messages[$error_code] ?? "Unknown upload error (code: {$error_code})";

        error_log("Avatar upload error: {$error_message} (code: {$error_code})");

        return [
            'success' => false,
            'message' => $error_message,
            'filename' => null
        ];
    }

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Check by extension first
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        return [
            'success' => false,
            'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.',
            'filename' => null
        ];
    }

    // Also check MIME type if available
    $file_type = $file['type'] ?? '';
    if ($file_type && !in_array($file_type, $allowed_types)) {
        return [
            'success' => false,
            'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.',
            'filename' => null
        ];
    }

    // Validate file size (max 2MB)
    $max_size = 2 * 1024 * 1024; // 2MB in bytes
    if ($file['size'] > $max_size) {
        return [
            'success' => false,
            'message' => 'File size too large. Maximum size is 2MB.',
            'filename' => null
        ];
    }

    // Create upload directory if it doesn't exist
    // __DIR__ is /path/to/travel-website/includes/
    // So we go up one level to get travel-website directory
    $base_dir = dirname(__DIR__); // Gets travel-website directory
    $full_upload_dir = $base_dir . '/' . trim($upload_dir, '/');

    // Normalize path separators and ensure it ends with /
    $full_upload_dir = rtrim(str_replace('\\', '/', $full_upload_dir), '/') . '/';

    // Try to get absolute path
    $resolved_base = realpath($base_dir);
    if ($resolved_base !== false) {
        $full_upload_dir = $resolved_base . '/' . trim($upload_dir, '/') . '/';
    }

    if (!file_exists($full_upload_dir)) {
        if (!@mkdir($full_upload_dir, 0775, true)) {
            $error_msg = "Failed to create upload directory: {$full_upload_dir} (resolved from: " . __DIR__ . "/../{$upload_dir})";
            error_log($error_msg);
            return [
                'success' => false,
                'message' => 'Failed to create upload directory. Please check server permissions.',
                'filename' => null
            ];
        }
        // Ensure parent directories also have correct permissions
        $parent_dir = dirname($full_upload_dir);
        if (file_exists($parent_dir) && !is_writable($parent_dir)) {
            @chmod($parent_dir, 0775);
        }
    }

    // Check if directory is writable
    if (!is_writable($full_upload_dir)) {
        // Try to fix permissions
        @chmod($full_upload_dir, 0775);

        // Also try to fix parent directory
        $parent_dir = dirname($full_upload_dir);
        if (file_exists($parent_dir)) {
            @chmod($parent_dir, 0775);
        }

        // Check again after fixing permissions
        if (!is_writable($full_upload_dir)) {
            $absolute_path = realpath($full_upload_dir) ?: $full_upload_dir;
            $error_msg = "Upload directory is not writable: {$absolute_path}. Current permissions: " . substr(sprintf('%o', fileperms($full_upload_dir)), -4);
            error_log($error_msg);

            // Provide more helpful error message
            return [
                'success' => false,
                'message' => 'Upload directory is not writable. Please ensure the uploads/avatars directory has write permissions (775).',
                'filename' => null
            ];
        }
    }

    // Generate unique filename (extension already validated above)
    $filename = uniqid('avatar_', true) . '.' . $extension;
    $filepath = $full_upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        $error_msg = "Failed to move uploaded file from {$file['tmp_name']} to {$filepath}";
        error_log($error_msg);

        // Check if temp file exists
        if (!file_exists($file['tmp_name'])) {
            return [
                'success' => false,
                'message' => 'Temporary file was not found. The upload may have timed out.',
                'filename' => null
            ];
        }

        // Check if destination directory is writable
        if (!is_writable($full_upload_dir)) {
            return [
                'success' => false,
                'message' => 'Cannot write to upload directory. Please check server permissions.',
                'filename' => null
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to save uploaded file. Please try again or contact support.',
            'filename' => null
        ];
    }

    return [
        'success' => true,
        'message' => 'Avatar uploaded successfully',
        'filename' => $upload_dir . $filename
    ];
}

/**
 * Update user avatar
 * @param int $user_id User ID
 * @param string $avatar_path Avatar file path
 * @return bool True on success, false on failure
 */
function update_user_avatar($user_id, $avatar_path)
{
    require_once __DIR__ . '/db.php';

    $conn = get_db_connection();
    if ($conn === null) {
        return false;
    }

    try {
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$avatar_path, $user_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Update avatar error: " . $e->getMessage());
        return false;
    }
}

/**
 * Register a new user
 * @param string $username Username
 * @param string $email Email address
 * @param string $password Plain text password
 * @param array|null $avatar_file $_FILES array element for avatar (optional)
 * @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
 */
function register_user($username, $email, $password, $avatar_file = null)
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

        // Handle avatar upload if provided
        $avatar_path = null;
        if ($avatar_file !== null && isset($avatar_file['tmp_name']) && $avatar_file['error'] === UPLOAD_ERR_OK) {
            $upload_result = upload_avatar($avatar_file);
            if ($upload_result['success'] && $upload_result['filename'] !== null) {
                $avatar_path = $upload_result['filename'];
            } elseif (!$upload_result['success']) {
                return [
                    'success' => false,
                    'message' => $upload_result['message'],
                    'user_id' => null
                ];
            }
        }

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, avatar, role) VALUES (?, ?, ?, ?, 'authenticated')");
        $stmt->execute([$username, $email, $hashed_password, $avatar_path]);

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
        $stmt = $conn->prepare("SELECT id, username, email, password, role, avatar FROM users WHERE username = ? OR email = ?");
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
                'role' => $user['role'],
                'avatar' => $user['avatar'] ?? null
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
