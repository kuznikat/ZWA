<?php

/**
 * Profile page - User personal cabinet
 * Displays user information, avatar, and allows role switching (for admins)
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

// Require login
require_login('profile.php');

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

// Get current user data
$user_id = get_user_id();
$user_data = get_user_data($user_id);
$errors = [];
$success_msg = '';

// Handle role switching (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_role']) && is_admin()) {
    $new_role = $_POST['new_role'] ?? '';

    if (in_array($new_role, ['authenticated', 'admin'])) {
        $conn = get_db_connection();
        if ($conn !== null) {
            try {
                $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$new_role, $user_id]);

                // Update session
                $_SESSION['role'] = $new_role;
                $user_data['role'] = $new_role;

                $success_msg = 'Role updated successfully!';
            } catch (PDOException $e) {
                error_log("Role switch error: " . $e->getMessage());
                $errors['general'] = 'Failed to update role. Please try again.';
            }
        }
    } else {
        $errors['general'] = 'Invalid role selected.';
    }
}

// Handle avatar update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_avatar'])) {
    // Check if cropped image data is provided (from canvas editor)
    $cropped_image_data = $_POST['cropped_image_data'] ?? null;

    if ($cropped_image_data && !empty(trim($cropped_image_data))) {
        // Handle cropped image from canvas
        $upload_result = upload_avatar_from_data($cropped_image_data);

        if ($upload_result['success'] && $upload_result['filename'] !== null) {
            // Delete old avatar if exists
            if ($user_data['avatar'] && file_exists(__DIR__ . '/' . $user_data['avatar'])) {
                @unlink(__DIR__ . '/' . $user_data['avatar']);
            }

            // Update database
            if (update_user_avatar($user_id, $upload_result['filename'])) {
                // Update session
                $_SESSION['avatar'] = $upload_result['filename'];
                $user_data['avatar'] = $upload_result['filename'];
                $success_msg = 'Avatar updated successfully!';
            } else {
                $errors['general'] = 'Failed to update avatar in database.';
            }
        } else {
            $errors['general'] = $upload_result['message'] ?? 'Failed to save avatar.';
        }
    } else {
        // Fallback to file upload (if no cropped data)
        $avatar_file = $_FILES['avatar'] ?? null;

        if ($avatar_file === null || !isset($avatar_file['tmp_name'])) {
            $errors['general'] = 'Please select an image file.';
        } elseif ($avatar_file['error'] !== UPLOAD_ERR_OK) {
            // Provide specific error messages for upload errors
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds maximum size (2MB). Please choose a smaller image.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds maximum size. Please choose a smaller image.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No file was selected. Please choose an image file.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server error: Missing temporary folder. Please contact support.',
                UPLOAD_ERR_CANT_WRITE => 'Server error: Cannot write file. Please contact support.',
                UPLOAD_ERR_EXTENSION => 'File upload was blocked. Please try a different image format.'
            ];

            $error_code = $avatar_file['error'];
            $errors['general'] = $error_messages[$error_code] ?? "Upload error occurred (code: {$error_code}). Please try again.";
        } else {
            $upload_result = upload_avatar($avatar_file);

            if ($upload_result['success'] && $upload_result['filename'] !== null) {
                // Delete old avatar if exists
                if ($user_data['avatar'] && file_exists(__DIR__ . '/' . $user_data['avatar'])) {
                    @unlink(__DIR__ . '/' . $user_data['avatar']);
                }

                // Update database
                if (update_user_avatar($user_id, $upload_result['filename'])) {
                    // Update session
                    $_SESSION['avatar'] = $upload_result['filename'];
                    $user_data['avatar'] = $upload_result['filename'];
                    $success_msg = 'Avatar updated successfully!';
                } else {
                    $errors['general'] = 'Failed to update avatar in database.';
                }
            } else {
                $errors['general'] = $upload_result['message'] ?? 'Failed to upload avatar.';
            }
        }
    }
}

// Get success message from session (if redirected)
if (isset($_SESSION['profile_success'])) {
    $success_msg = $_SESSION['profile_success'];
    unset($_SESSION['profile_success']);
}

// Get avatar path or default
$avatar_path = $user_data['avatar'] ?? null;
if ($avatar_path && file_exists(__DIR__ . '/' . $avatar_path)) {
    $avatar_url = $avatar_path;
} else {
    $avatar_url = 'images/pic-1.png'; // Default avatar
}

/**
 * Format date for display
 * @param string $date Date string
 * @return string Formatted date
 */
function format_date($date)
{
    if (empty($date)) {
        return '';
    }
    try {
        $date_obj = new DateTime($date);
        return $date_obj->format('F j, Y');
    } catch (Exception $e) {
        return $date;
    }
}

// Fetch upcoming bookings (leaving date >= today)
$upcoming_bookings = [];
$conn = get_db_connection();
if ($conn !== null) {
    try {
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT id, location, arrivals, leaving, guests, created_at 
                                FROM bookings 
                                WHERE user_id = ? AND leaving >= ? 
                                ORDER BY arrivals ASC 
                                LIMIT 5");
        $stmt->execute([$user_id, $today]);
        $upcoming_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Fetch upcoming bookings error: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>

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
                <a href="profile.php">profile</a>
                <a href="logout.php">logout</a>
                <?php if (is_admin()): ?>
                    <a href="admin/tours.php">admin</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php">login</a>
                <a href="register.php">register</a>
            <?php endif; ?>
        </nav>

        <div id="menu-btn" class="menu-icon">☰</div>

    </section>

    <!-- header section ends -->

    <div class="heading heading-book">
        <h1>my profile</h1>
    </div>

    <!-- profile section starts  -->

    <section class="booking">

        <h1 class="heading-title">personal cabinet</h1>

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

        <div class="profile-container">

            <!-- User Info Card -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="profile-avatar-wrapper">
                        <img src="<?php echo escape($avatar_url); ?>" alt="Avatar" class="profile-avatar">
                    </div>
                    <div class="profile-info">
                        <h2 class="profile-username">
                            <?php echo escape($user_data['username']); ?>
                        </h2>
                        <p class="profile-info-item">
                            <strong>Email:</strong> <?php echo escape($user_data['email']); ?>
                        </p>
                        <p class="profile-info-item">
                            <strong>Role:</strong>
                            <span class="profile-role">
                                <?php echo escape($user_data['role']); ?>
                            </span>
                        </p>
                        <p class="profile-info-item-small">
                            <strong>Member since:</strong>
                            <?php
                            $created_at = new DateTime($user_data['created_at']);
                            echo escape($created_at->format('F j, Y'));
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Update Avatar Form -->
            <div class="profile-card">
                <h3 class="profile-card-title">Update Avatar</h3>
                <form action="" method="post" enctype="multipart/form-data" class="book-form" id="avatar-form">
                    <div class="flex">
                        <div class="inputBox">
                            <label for="avatar">select new avatar:</label>
                            <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small>Max size: 2MB. Allowed: JPEG, PNG, GIF, WebP</small>
                        </div>
                    </div>

                    <!-- Image Editor Container -->
                    <div id="avatar-editor-container" class="avatar-editor-container">
                        <div class="avatar-editor-header">
                            <h4 class="avatar-editor-title">Adjust Your Avatar</h4>
                            <p class="avatar-editor-description">Drag to move • Scroll to zoom • Preview below</p>
                        </div>

                        <!-- Canvas Container -->
                        <div class="avatar-canvas-wrapper">
                            <div class="avatar-canvas-container">
                                <canvas id="avatar-canvas"></canvas>
                            </div>
                        </div>

                        <!-- Controls -->
                        <div class="avatar-controls">
                            <button type="button" id="zoom-in-btn" class="btn avatar-control-btn">Zoom In</button>
                            <button type="button" id="zoom-out-btn" class="btn avatar-control-btn">Zoom Out</button>
                            <button type="button" id="reset-avatar-btn" class="btn avatar-control-btn">Reset</button>
                            <button type="button" id="cancel-avatar-btn" class="btn avatar-cancel-btn">Cancel</button>
                        </div>

                        <!-- Preview -->
                        <div class="avatar-preview-section">
                            <h4 class="avatar-preview-title">Preview</h4>
                            <div class="avatar-preview-container">
                                <canvas id="avatar-preview"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden input for cropped image -->
                    <input type="hidden" id="cropped-image-data" name="cropped_image_data">

                    <input type="submit" value="update avatar" class="btn" name="update_avatar" id="avatar-submit-btn">
                </form>
            </div>

            <!-- Upcoming Bookings -->
            <div class="profile-card">
                <h3 class="profile-card-title">Upcoming Tours</h3>
                <?php if (empty($upcoming_bookings)): ?>
                    <p class="profile-card-description">
                        You don't have any upcoming tours booked yet.
                    </p>
                    <a href="book.php" class="btn profile-book-btn">Book a Tour</a>
                <?php else: ?>
                    <div class="bookings-list-simple">
                        <?php foreach ($upcoming_bookings as $booking): ?>
                            <div class="booking-item-simple">
                                <div class="booking-item-content">
                                    <h4>
                                        <?php echo escape($booking['location']); ?>
                                    </h4>
                                    <div class="booking-item-details">
                                        <div>
                                            <strong>Arrival:</strong> <?php echo escape(format_date($booking['arrivals'])); ?>
                                        </div>
                                        <div>
                                            <strong>Leaving:</strong> <?php echo escape(format_date($booking['leaving'])); ?>
                                        </div>
                                        <div>
                                            <strong>Guests:</strong> <?php echo escape($booking['guests']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="profile-bookings-link">
                        <a href="my-bookings.php" class="btn">View All Bookings</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Role Switcher (Admin Only) -->
            <?php if (is_admin()): ?>
                <div class="profile-card">
                    <h3 class="profile-card-title">Role Switcher (Testing)</h3>
                    <p class="profile-card-description">
                        Switch between admin and authenticated user roles for testing purposes.
                    </p>
                    <form action="" method="post" class="book-form">
                        <div class="flex">
                            <div class="inputBox">
                                <label for="new_role">select role:</label>
                                <select id="new_role" name="new_role" class="profile-select">
                                    <option value="authenticated" <?php echo ($user_data['role'] === 'authenticated') ? 'selected' : ''; ?>>Authenticated User</option>
                                    <option value="admin" <?php echo ($user_data['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                        </div>
                        <input type="submit" value="switch role" class="btn" name="switch_role">
                    </form>
                </div>
            <?php endif; ?>

        </div>

    </section>

    <!-- profile section ends -->


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
    <script src="js/avatar-editor.js"></script>

</body>

</html>
<?php
// Flush output buffer
ob_end_flush();
?>