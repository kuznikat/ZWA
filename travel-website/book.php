<?php

/**
 * Booking page - Handles trip booking form submission
 * Implements: Server-side validation, POST-Redirect-GET pattern, XSS protection, SQL injection prevention
 */

// Start output buffering to catch any errors
ob_start();

// Error reporting (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, but log them
ini_set('log_errors', 1);

// Start a session for flash messages
if (session_status() === PHP_SESSION_NONE) {
   @session_start();
}

// Include database connection and authentication
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Initialize error variable
$db_error = null;

// Fetch available tours
$available_tours = [];
$conn = get_db_connection();
if ($conn !== null) {
   try {
      $stmt = $conn->query("SELECT t.*, 
                              COALESCE(SUM(b.guests), 0) as booked_guests,
                              (t.capacity - COALESCE(SUM(b.guests), 0)) as remaining_spots
                              FROM tours t
                              LEFT JOIN bookings b ON t.id = b.tour_id
                              GROUP BY t.id
                              HAVING remaining_spots > 0
                              ORDER BY t.title ASC");
      $available_tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
   } catch (PDOException $e) {
      error_log("Fetch tours error: " . $e->getMessage());
   }
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

/**
 * Validate name field
 * @param string $name Name to validate
 * @return string|null Error message or null if valid
 */
function validate_name($name)
{
   if (empty(trim($name))) {
      return "Name is required";
   }
   $name = trim($name);
   if (strlen($name) > 50) {
      return "Name must be 50 characters or less";
   }
   if (!preg_match("/^[a-zA-Z\s'-]+$/", $name)) {
      return "Name can only contain letters, spaces, hyphens, and apostrophes";
   }
   return null;
}

/**
 * Validate email field
 * @param string $email Email to validate
 * @return string|null Error message or null if valid
 */
function validate_email($email)
{
   if (empty(trim($email))) {
      return "Email is required";
   }
   $email = trim($email);
   if (strlen($email) > 100) {
      return "Email must be 100 characters or less";
   }
   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return "Please enter a valid email address";
   }
   return null;
}

/**
 * Validate phone field
 * @param string $phone Phone to validate
 * @return string|null Error message or null if valid
 */
function validate_phone($phone)
{
   if (empty(trim($phone))) {
      return "Phone number is required";
   }
   $phone = trim($phone);
   // Remove spaces, dashes, parentheses for validation
   $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
   if (!preg_match("/^[0-9]{7,15}$/", $cleaned)) {
      return "Phone number must be 7-15 digits";
   }
   return null;
}

/**
 * Validate address field
 * @param string $address Address to validate
 * @return string|null Error message or null if valid
 */
function validate_address($address)
{
   if (empty(trim($address))) {
      return "Address is required";
   }
   $address = trim($address);
   if (strlen($address) > 100) {
      return "Address must be 100 characters or less";
   }
   return null;
}

/**
 * Validate location field
 * @param string $location Location to validate
 * @return string|null Error message or null if valid
 */
function validate_location($location)
{
   if (empty(trim($location))) {
      return "Destination location is required";
   }
   $location = trim($location);
   if (strlen($location) > 50) {
      return "Location must be 50 characters or less";
   }
   return null;
}

/**
 * Validate guests field
 * @param string $guests Number of guests
 * @return string|null Error message or null if valid
 */
function validate_guests($guests)
{
   if (empty(trim($guests))) {
      return "Number of guests is required";
   }
   $guests = intval($guests);
   if ($guests < 1 || $guests > 50) {
      return "Number of guests must be between 1 and 50";
   }
   return null;
}

/**
 * Validate date field
 * @param string $date Date to validate
 * @param string $field_name Name of the field for the error message
 * @return string|null Error message or null if valid
 */
function validate_date($date, $field_name)
{
   if (empty(trim($date))) {
      return ucfirst($field_name) . " date is required";
   }
   $date = trim($date);
   // Check if the date is a valid format
   $d = DateTime::createFromFormat('Y-m-d', $date);
   if (!$d || $d->format('Y-m-d') !== $date) {
      return "Please enter a valid date";
   }
   // Check if the date is not in the past
   $today = new DateTime();
   $today->setTime(0, 0);
   $input_date = new DateTime($date);
   $input_date->setTime(0, 0);
   if ($input_date < $today) {
      return ucfirst($field_name) . " date cannot be in the past";
   }
   return null;
}

/**
 * Validate date dependency (leaving date must be after arrival date)
 * @param string $arrivals Arrival date
 * @param string $leaving Leaving date
 * @return string|null Error message or null if valid
 */
function validate_date_dependency($arrivals, $leaving)
{
   if (empty($arrivals) || empty($leaving)) {
      return null; // Let individual date validation handle empty fields
   }
   $arrivals_date = new DateTime($arrivals);
   $leaving_date = new DateTime($leaving);
   if ($leaving_date <= $arrivals_date) {
      return "Leaving date must be after arrival date";
   }
   return null;
}

// Initialize variables
$name = $email = $phone = $address = $location = $guests = $arrivals = $leaving = '';
$tour_id = isset($_GET['tour']) ? (int)$_GET['tour'] : 0;
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
   // Get and trim form data (store exactly as entered, but trim whitespace)
   $name = trim($_POST['name'] ?? '');
   $email = trim($_POST['email'] ?? '');
   $phone = trim($_POST['phone'] ?? '');
   $address = trim($_POST['address'] ?? '');
   $tour_id = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
   $location = trim($_POST['location'] ?? '');
   $guests = trim($_POST['guests'] ?? '');
   $arrivals = trim($_POST['arrivals'] ?? '');
   $leaving = trim($_POST['leaving'] ?? '');

   // Server-side validation-TODO Phase 4
   $error = validate_name($name);
   if ($error) $errors['name'] = $error;

   $error = validate_email($email);
   if ($error) $errors['email'] = $error;

   $error = validate_phone($phone);
   if ($error) $errors['phone'] = $error;

   $error = validate_address($address);
   if ($error) $errors['address'] = $error;

   // Validate tour selection
   if ($tour_id <= 0) {
      $errors['tour_id'] = 'Please select a tour';
   } else {
      // Check tour capacity
      $capacity_check = check_tour_capacity($tour_id, (int)$guests);
      if (!$capacity_check['available']) {
         $errors['guests'] = $capacity_check['message'];
      }
   }

   $error = validate_location($location);
   if ($error) $errors['location'] = $error;

   $error = validate_guests($guests);
   if ($error) $errors['guests'] = $error;

   $error = validate_date($arrivals, 'arrival');
   if ($error) $errors['arrivals'] = $error;

   $error = validate_date($leaving, 'leaving');
   if ($error) $errors['leaving'] = $error;

   // Validate date dependency
   $error = validate_date_dependency($arrivals, $leaving);
   if ($error) $errors['leaving'] = $error;

   // If no errors, insert into a database
   if (empty($errors)) {
      // Get database connection (lazy loading - only connect when needed)
      $conn = get_db_connection();

      if ($conn === null) {
         $db_error = get_db_error();
         $errors['database'] = $db_error ?: "Database connection failed. Please try again later.";
      } else {
         try {
            // Prepare statement to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO `bookings` (user_id, tour_id, name, email, phone, address, location, guests, arrivals, leaving) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Get user_id if logged in
            $user_id = is_logged_in() ? get_user_id() : null;

            // Execute with parameters (data stored exactly as entered)
            $stmt->execute([
               $user_id, // user_id (NULL for unauthenticated users, set for logged-in users)
               $tour_id, // tour_id from selected tour
               $name, // Store exactly as entered
               $email,
               $phone,
               $address,
               $location,
               intval($guests), // Convert to integer for database
               $arrivals,
               $leaving
            ]);

            // Set the success message in session for a POST-Redirect-GET pattern
            $_SESSION['booking_success'] = 'Booking submitted successfully!';

            // Redirect to prevent duplicate submissions
            if (!headers_sent()) {
               header('Location: book.php');
               exit();
            } else {
               // If headers already sent, use JavaScript redirect as fallback
               echo '<script>window.location.href = "book.php";</script>';
               exit();
            }
         } catch (PDOException $e) {
            $errors['database'] = "An error occurred. Please try again later.";
            // Log error (in production, log to file instead of displaying)
            error_log("Booking error: " . $e->getMessage());
         }
      }
   }
}

// Get success message from session (POST-Redirect-GET pattern)
$success_msg = '';
if (isset($_SESSION['booking_success'])) {
   $success_msg = $_SESSION['booking_success'];
   unset($_SESSION['booking_success']); // Clear after displaying
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>book</title>

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
      <h1>book now</h1>
   </div>

   <!-- the booking section starts  -->

   <section class="booking">

      <h1 class="heading-title">book your trip!</h1>

      <?php if ($success_msg): ?>
         <div class="success-message">
            <?php echo escape($success_msg); ?>
         </div>
      <?php endif; ?>

      <?php if (isset($errors['database'])): ?>
         <div class="error-message">
            <?php echo escape($errors['database']); ?>
         </div>
      <?php endif; ?>

      <?php if (isset($db_error)): ?>
         <div class="error-message">
            <?php echo escape($db_error); ?>
         </div>
      <?php endif; ?>

      <form action="" method="post" class="book-form">

         <div class="flex">
            <div class="inputBox">
               <label for="name">name: <span>*</span></label>
               <input type="text" id="name" placeholder="enter your name" maxlength="50" name="name" value="<?php echo escape($name); ?>" required>
               <?php if (isset($errors['name'])): ?>
                  <span class="error-text"><?php echo escape($errors['name']); ?></span>
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
               <label for="phone">phone: <span>*</span></label>
               <input type="tel" id="phone" placeholder="enter your number" name="phone" value="<?php echo escape($phone); ?>" required>
               <?php if (isset($errors['phone'])): ?>
                  <span class="error-text"><?php echo escape($errors['phone']); ?></span>
               <?php endif; ?>
            </div>
            <div class="inputBox">
               <label for="address">address: <span>*</span></label>
               <input type="text" id="address" maxlength="100" placeholder="enter your address" name="address" value="<?php echo escape($address); ?>" required>
               <?php if (isset($errors['address'])): ?>
                  <span class="error-text"><?php echo escape($errors['address']); ?></span>
               <?php endif; ?>
            </div>
            <div class="inputBox">
               <label for="tour_id">select tour: <span>*</span></label>
               <select id="tour_id" name="tour_id" required>
                  <option value="">-- Select a tour --</option>
                  <?php foreach ($available_tours as $tour): ?>
                     <option value="<?php echo escape($tour['id']); ?>"
                        <?php echo ($tour_id == $tour['id']) ? 'selected' : ''; ?>
                        data-remaining="<?php echo escape($tour['remaining_spots']); ?>">
                        <?php echo escape($tour['title']); ?> - <?php echo escape($tour['location']); ?>
                        (<?php echo escape($tour['remaining_spots']); ?> spots left)
                     </option>
                  <?php endforeach; ?>
               </select>
               <?php if (isset($errors['tour_id'])): ?>
                  <span class="error-text"><?php echo escape($errors['tour_id']); ?></span>
               <?php endif; ?>
               <small class="form-helper-text" id="capacity-info"></small>
            </div>
            <div class="inputBox">
               <label for="location">where to: <span>*</span></label>
               <input type="text" id="location" placeholder="place you want to visit" name="location" maxlength="100" value="<?php echo escape($location); ?>" required>
               <?php if (isset($errors['location'])): ?>
                  <span class="error-text"><?php echo escape($errors['location']); ?></span>
               <?php endif; ?>
            </div>
            <div class="inputBox">
               <label for="guests">how many: <span>*</span></label>
               <input type="number" id="guests" min="1" max="50" placeholder="number of guests" name="guests" value="<?php echo escape($guests); ?>" required>
               <?php if (isset($errors['guests'])): ?>
                  <span class="error-text"><?php echo escape($errors['guests']); ?></span>
               <?php endif; ?>
            </div>
            <div class="inputBox">
               <label for="arrivals">arrivals: <span>*</span></label>
               <input type="date" id="arrivals" name="arrivals" value="<?php echo escape($arrivals); ?>" required>
               <?php if (isset($errors['arrivals'])): ?>
                  <span class="error-text"><?php echo escape($errors['arrivals']); ?></span>
               <?php endif; ?>
            </div>
            <div class="inputBox">
               <label for="leaving">leaving: <span>*</span></label>
               <input type="date" id="leaving" name="leaving" value="<?php echo escape($leaving); ?>" required>
               <?php if (isset($errors['leaving'])): ?>
                  <span class="error-text"><?php echo escape($errors['leaving']); ?></span>
               <?php endif; ?>
            </div>
         </div>

         <input type="submit" value="submit" class="btn" name="send">

      </form>

   </section>

   <!-- booking section ends -->


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
   <script src="js/validation.js"></script>
   <script>
      // Update capacity info when tour is selected
      document.getElementById('tour_id')?.addEventListener('change', function() {
         const select = this;
         const selectedOption = select.options[select.selectedIndex];
         const capacityInfo = document.getElementById('capacity-info');
         const guestsInput = document.getElementById('guests');

         if (selectedOption.value && capacityInfo) {
            const remaining = parseInt(selectedOption.dataset.remaining || 0);
            if (remaining > 0) {
               capacityInfo.textContent = `${remaining} spot(s) remaining`;
               capacityInfo.style.color = remaining <= 5 ? '#e74c3c' : '#27ae60';
               if (guestsInput) {
                  guestsInput.max = remaining;
               }
            } else {
               capacityInfo.textContent = 'Tour is fully booked';
               capacityInfo.style.color = '#e74c3c';
            }
         } else if (capacityInfo) {
            capacityInfo.textContent = '';
         }
      });

      // Trigger on page load if tour is pre-selected
      document.addEventListener('DOMContentLoaded', function() {
         const tourSelect = document.getElementById('tour_id');
         if (tourSelect && tourSelect.value) {
            tourSelect.dispatchEvent(new Event('change'));
         }
      });
   </script>

</body>

</html>
<?php
// Flush output buffer
ob_end_flush();
?>