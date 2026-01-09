<?php

/**
 * My Bookings page - Display user's own bookings
 * Implements: Access control, filtering, sorting, XSS protection
 */

// Start output buffering
ob_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Include authentication and database functions
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Require login
require_login('my-bookings.php');

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

// Get current user ID
$user_id = get_user_id();
$bookings = [];
$errors = [];

// Get filter and sort parameters
$filter_location = $_GET['location'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';
$sort_by = $_GET['sort'] ?? 'created_at';
$sort_order = $_GET['order'] ?? 'desc';

// Validate sort parameters
$allowed_sort_fields = ['created_at', 'arrivals', 'leaving', 'location', 'guests'];
$sort_by = in_array($sort_by, $allowed_sort_fields) ? $sort_by : 'created_at';
$sort_order = strtolower($sort_order) === 'asc' ? 'asc' : 'desc';

// Fetch bookings from database
$conn = get_db_connection();
if ($conn === null) {
    $errors[] = 'Database connection failed. Please try again later.';
} else {
    try {
        // Build query with filters
        $query = "SELECT id, name, email, phone, address, location, guests, arrivals, leaving, created_at 
                  FROM bookings 
                  WHERE user_id = ?";
        $params = [$user_id];

        // Add location filter
        if (!empty($filter_location)) {
            $query .= " AND location LIKE ?";
            $params[] = '%' . $filter_location . '%';
        }

        // Add date range filter
        if (!empty($filter_date_from)) {
            $query .= " AND arrivals >= ?";
            $params[] = $filter_date_from;
        }
        if (!empty($filter_date_to)) {
            $query .= " AND leaving <= ?";
            $params[] = $filter_date_to;
        }

        // Add sorting
        $query .= " ORDER BY " . $sort_by . " " . $sort_order;

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Fetch bookings error: " . $e->getMessage());
        $errors[] = 'An error occurred while fetching your bookings.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>

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
                <a href="my-bookings.php">my bookings</a>
                <a href="logout.php">logout</a>
                <?php if (is_admin()): ?>
                    <a href="#">admin</a>
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
        <h1>my bookings</h1>
    </div>

    <!-- bookings section starts  -->

    <section class="booking">

        <h1 class="heading-title">your travel bookings</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo escape($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Filter and Sort Form -->
        <div class="bookings-filters">
            <form method="get" action="" class="book-form">
                <div class="flex">
                    <div class="inputBox">
                        <label for="location">filter by location:</label>
                        <input type="text" id="location" name="location" placeholder="enter location" value="<?php echo escape($filter_location); ?>">
                    </div>
                    <div class="inputBox">
                        <label for="date_from">from date:</label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo escape($filter_date_from); ?>">
                    </div>
                    <div class="inputBox">
                        <label for="date_to">to date:</label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo escape($filter_date_to); ?>">
                    </div>
                    <div class="inputBox">
                        <label for="sort">sort by:</label>
                        <select id="sort" name="sort" class="profile-select">
                            <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>Booking Date</option>
                            <option value="arrivals" <?php echo $sort_by === 'arrivals' ? 'selected' : ''; ?>>Arrival Date</option>
                            <option value="leaving" <?php echo $sort_by === 'leaving' ? 'selected' : ''; ?>>Leaving Date</option>
                            <option value="location" <?php echo $sort_by === 'location' ? 'selected' : ''; ?>>Location</option>
                            <option value="guests" <?php echo $sort_by === 'guests' ? 'selected' : ''; ?>>Number of Guests</option>
                        </select>
                    </div>
                    <div class="inputBox">
                        <label for="order">order:</label>
                        <select id="order" name="order" class="profile-select">
                            <option value="desc" <?php echo $sort_order === 'desc' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="asc" <?php echo $sort_order === 'asc' ? 'selected' : ''; ?>>Oldest First</option>
                        </select>
                    </div>
                </div>
                <div class="filters-actions">
                    <input type="submit" value="apply filters" class="btn" name="filter">
                    <a href="my-bookings.php" class="btn clear-filters">clear filters</a>
                </div>
            </form>
        </div>

        <!-- Bookings List -->
        <?php if (empty($bookings)): ?>
            <div class="success-message bookings-empty-message">
                <p>You don't have any bookings yet.</p>
                <a href="book.php">Make your first booking here</a>
            </div>
        <?php else: ?>
            <div class="bookings-list">
                <p>
                    Found <?php echo count($bookings); ?> booking<?php echo count($bookings) !== 1 ? 's' : ''; ?>
                </p>

                <div class="bookings-container">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-card-header">
                                <div class="booking-card-content">
                                    <h3 class="booking-card-title">
                                        <?php echo escape($booking['location']); ?>
                                    </h3>
                                    <div class="booking-card-details">
                                        <div>
                                            <strong>Name:</strong> <?php echo escape($booking['name']); ?>
                                        </div>
                                        <div>
                                            <strong>Email:</strong> <?php echo escape($booking['email']); ?>
                                        </div>
                                        <div>
                                            <strong>Phone:</strong> <?php echo escape($booking['phone']); ?>
                                        </div>
                                        <div>
                                            <strong>Address:</strong> <?php echo escape($booking['address']); ?>
                                        </div>
                                        <div>
                                            <strong>Guests:</strong> <?php echo escape($booking['guests']); ?>
                                        </div>
                                        <div>
                                            <strong>Arrival:</strong> <?php echo escape(format_date($booking['arrivals'])); ?>
                                        </div>
                                        <div>
                                            <strong>Leaving:</strong> <?php echo escape(format_date($booking['leaving'])); ?>
                                        </div>
                                        <div>
                                            <strong>Booked on:</strong> <?php echo escape(format_date($booking['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="booking-card-actions">
                                    <a href="edit-booking.php?id=<?php echo escape($booking['id']); ?>" class="btn">edit</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </section>

    <!-- bookings section ends -->


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