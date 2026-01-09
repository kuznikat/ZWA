<?php

/**
 * Admin Tours Management Page
 * Allows admins to manage tour capacity and tour details
 */

// Start output buffering
ob_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Include authentication and database functions
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Require admin access
require_admin();

/**
 * Escape HTML output to prevent XSS
 */
function escape($string)
{
    if ($string === null) {
        return '';
    }
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

$success_message = '';
$error_message = '';
$tours = [];
$errors = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $tour_id = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $date = $_POST['date'] ?? '';
        $capacity = isset($_POST['capacity']) ? (int)$_POST['capacity'] : 50;
        $image = trim($_POST['image'] ?? '');

        // Validation
        if (empty($title)) {
            $errors[] = 'Title is required';
        }
        if (empty($description)) {
            $errors[] = 'Description is required';
        }
        if (empty($location)) {
            $errors[] = 'Location is required';
        }
        if ($price <= 0) {
            $errors[] = 'Price must be greater than 0';
        }
        if (empty($date)) {
            $errors[] = 'Date is required';
        }
        if ($capacity <= 0) {
            $errors[] = 'Capacity must be greater than 0';
        }
        if (empty($image)) {
            $errors[] = 'Image path is required';
        }

        if (empty($errors)) {
            $conn = get_db_connection();
            if ($conn === null) {
                $error_message = 'Database connection failed';
            } else {
                try {
                    if ($action === 'add') {
                        $stmt = $conn->prepare("INSERT INTO tours (title, description, location, price, date, image, capacity) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $description, $location, $price, $date, $image, $capacity]);
                        $success_message = 'Tour added successfully';
                    } else {
                        $stmt = $conn->prepare("UPDATE tours SET title = ?, description = ?, location = ?, price = ?, date = ?, image = ?, capacity = ? WHERE id = ?");
                        $stmt->execute([$title, $description, $location, $price, $date, $image, $capacity, $tour_id]);
                        $success_message = 'Tour updated successfully';
                    }
                } catch (PDOException $e) {
                    error_log("Tour save error: " . $e->getMessage());
                    $error_message = 'An error occurred while saving the tour';
                }
            }
        } else {
            $error_message = implode(', ', $errors);
        }
    } elseif ($action === 'delete') {
        $tour_id = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
        if ($tour_id > 0) {
            $conn = get_db_connection();
            if ($conn === null) {
                $error_message = 'Database connection failed';
            } else {
                try {
                    $stmt = $conn->prepare("DELETE FROM tours WHERE id = ?");
                    $stmt->execute([$tour_id]);
                    $success_message = 'Tour deleted successfully';
                } catch (PDOException $e) {
                    error_log("Tour delete error: " . $e->getMessage());
                    $error_message = 'An error occurred while deleting the tour';
                }
            }
        }
    }
}

// Fetch all tours
$conn = get_db_connection();
if ($conn !== null) {
    try {
        $stmt = $conn->query("SELECT t.*, 
                              COALESCE(SUM(b.guests), 0) as booked_guests,
                              (t.capacity - COALESCE(SUM(b.guests), 0)) as remaining_spots
                              FROM tours t
                              LEFT JOIN bookings b ON t.id = b.tour_id
                              GROUP BY t.id
                              ORDER BY t.created_at DESC");
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Fetch tours error: " . $e->getMessage());
        $error_message = 'An error occurred while fetching tours';
    }
}

// Get tour for editing
$edit_tour = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    foreach ($tours as $tour) {
        if ($tour['id'] == $edit_id) {
            $edit_tour = $tour;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tours Management</title>

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <!-- header section starts  -->

    <section class="header">

        <a href="../home.php" class="logo">Lets travel</a>

        <nav class="navbar">
            <a href="../home.php">home</a>
            <a href="../about.php">about</a>
            <a href="../package.php">package</a>
            <a href="../book.php">book</a>
            <?php if (is_logged_in()): ?>
                <a href="../profile.php">profile</a>
                <a href="../my-bookings.php">my bookings</a>
                <?php if (is_admin()): ?>
                    <a href="tours.php">admin</a>
                <?php endif; ?>
                <a href="../logout.php">logout</a>
            <?php else: ?>
                <a href="../login.php">login</a>
                <a href="../register.php">register</a>
            <?php endif; ?>
        </nav>

        <div id="menu-btn" class="menu-icon">☰</div>

    </section>

    <!-- header section ends -->

    <div class="heading">
        <h1>Tour Management</h1>
    </div>

    <!-- Admin Tours Section -->

    <section class="admin-section">
        <div class="admin-container">

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo escape($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo escape($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Add/Edit Tour Form -->
            <div class="admin-card">
                <h2 class="admin-card-title">
                    <?php echo $edit_tour ? 'Edit Tour' : 'Add New Tour'; ?>
                </h2>
                <form method="POST" class="admin-form">
                    <input type="hidden" name="action" value="<?php echo $edit_tour ? 'edit' : 'add'; ?>">
                    <?php if ($edit_tour): ?>
                        <input type="hidden" name="tour_id" value="<?php echo escape($edit_tour['id']); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" required
                            value="<?php echo escape($edit_tour['title'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="4" required><?php echo escape($edit_tour['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" required
                            value="<?php echo escape($edit_tour['location'] ?? ''); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required
                                value="<?php echo escape($edit_tour['price'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="capacity">Capacity *</label>
                            <input type="number" id="capacity" name="capacity" min="1" required
                                value="<?php echo escape($edit_tour['capacity'] ?? '50'); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date">Date *</label>
                            <input type="date" id="date" name="date" required
                                value="<?php echo escape($edit_tour['date'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="image">Image Path *</label>
                            <input type="text" id="image" name="image" required
                                value="<?php echo escape($edit_tour['image'] ?? ''); ?>"
                                placeholder="images/tour.jpg">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">
                            <?php echo $edit_tour ? 'Update Tour' : 'Add Tour'; ?>
                        </button>
                        <?php if ($edit_tour): ?>
                            <a href="tours.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tours List -->
            <div class="admin-card">
                <h2 class="admin-card-title">All Tours</h2>
                <div class="tours-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Date</th>
                                <th>Price</th>
                                <th>Capacity</th>
                                <th>Booked</th>
                                <th>Remaining</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tours)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No tours found. Add your first tour above.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tours as $tour): ?>
                                    <tr>
                                        <td><?php echo escape($tour['id']); ?></td>
                                        <td><?php echo escape($tour['title']); ?></td>
                                        <td><?php echo escape($tour['location']); ?></td>
                                        <td><?php echo escape($tour['date']); ?></td>
                                        <td>$<?php echo number_format($tour['price'], 2); ?></td>
                                        <td><?php echo escape($tour['capacity']); ?></td>
                                        <td><?php echo escape($tour['booked_guests']); ?></td>
                                        <td>
                                            <span class="capacity-badge <?php echo (int)$tour['remaining_spots'] <= 5 ? 'capacity-low' : ''; ?>">
                                                <?php echo escape($tour['remaining_spots']); ?>
                                            </span>
                                        </td>
                                        <td class="action-buttons">
                                            <a href="?edit=<?php echo escape($tour['id']); ?>" class="btn btn-small">Edit</a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this tour?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="tour_id" value="<?php echo escape($tour['id']); ?>">
                                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>

    <!-- custom js file link  -->
    <script src="../js/script.js"></script>

</body>

</html>

<?php
ob_end_flush();
?>