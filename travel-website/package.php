<?php

/**
 * Packages page - Displays travel packages with server-side pagination
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

// Fetch packages from database
$packages = [];
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
      $db_packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Format packages for display
      foreach ($db_packages as $pkg) {
         $packages[] = [
            'id' => $pkg['id'],
            'title' => $pkg['title'],
            'description' => $pkg['description'],
            'image' => $pkg['image'] ?: 'images/default.jpg',
            'alt' => strtolower(str_replace(' ', '-', $pkg['title'])),
            'remaining_spots' => (int)$pkg['remaining_spots'],
            'capacity' => (int)$pkg['capacity'],
            'location' => $pkg['location']
         ];
      }
   } catch (PDOException $e) {
      error_log("Fetch packages error: " . $e->getMessage());
      // Fallback to empty array if database query fails
      $packages = [];
   }
}

// Pagination settings
$items_per_page = 6;
$total_items = count($packages);
$total_pages = ceil($total_items / $items_per_page);

// Get current page from URL parameter
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Validate page number
if ($current_page < 1) {
   $current_page = 1;
} elseif ($current_page > $total_pages && $total_pages > 0) {
   $current_page = $total_pages;
}

// Calculate offset
$offset = ($current_page - 1) * $items_per_page;

// Get items for current page
$current_page_items = array_slice($packages, $offset, $items_per_page);

?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>package</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <!-- header section starts  -->

   <section class="header">

      <a href="home.php" class="logo"> Lets travel</a>

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

   <div class="heading heading-package">
      <h1>packages</h1>
   </div>

   <!-- packages section starts  -->

   <section class="packages">

      <h1 class="heading-title">top destinations</h1>

      <div class="box-container">

         <?php foreach ($current_page_items as $package): ?>
            <div class="box">
               <div class="image">
                  <img src="<?php echo escape($package['image']); ?>" alt="<?php echo escape($package['alt']); ?>">
                  <?php if (isset($package['remaining_spots'])): ?>
                     <div class="package-capacity-badge <?php echo $package['remaining_spots'] <= 5 ? 'capacity-low' : ''; ?>">
                        <?php if ($package['remaining_spots'] > 0): ?>
                           <?php echo escape($package['remaining_spots']); ?> spot<?php echo $package['remaining_spots'] != 1 ? 's' : ''; ?> left
                        <?php else: ?>
                           Fully booked
                        <?php endif; ?>
                     </div>
                  <?php endif; ?>
               </div>
               <div class="content">
                  <h3><?php echo escape($package['title']); ?></h3>
                  <p><?php echo nl2br(escape($package['description'])); ?></p>
                  <a href="book.php<?php echo isset($package['id']) ? '?tour=' . escape($package['id']) : ''; ?>"
                     class="btn <?php echo (isset($package['remaining_spots']) && $package['remaining_spots'] <= 0) ? 'btn-disabled' : ''; ?>">
                     <?php echo (isset($package['remaining_spots']) && $package['remaining_spots'] <= 0) ? 'Fully Booked' : 'book now'; ?>
                  </a>
               </div>
            </div>
         <?php endforeach; ?>

      </div>

      <!-- Pagination controls -->
      <?php if ($total_pages > 1): ?>
         <div class="pagination">
            <?php if ($current_page > 1): ?>
               <a href="?page=<?php echo $current_page - 1; ?>" class="pagination-btn">← Previous</a>
            <?php else: ?>
               <span class="pagination-btn disabled">← Previous</span>
            <?php endif; ?>

            <div class="pagination-numbers">
               <?php
               // Calculate page range to show
               $start_page = max(1, $current_page - 2);
               $end_page = min($total_pages, $current_page + 2);

               // Show first page if not in range
               if ($start_page > 1): ?>
                  <a href="?page=1" class="pagination-number">1</a>
                  <?php if ($start_page > 2): ?>
                     <span class="pagination-ellipsis">...</span>
                  <?php endif; ?>
               <?php endif; ?>

               <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                  <?php if ($i == $current_page): ?>
                     <span class="pagination-number active"><?php echo $i; ?></span>
                  <?php else: ?>
                     <a href="?page=<?php echo $i; ?>" class="pagination-number"><?php echo $i; ?></a>
                  <?php endif; ?>
               <?php endfor; ?>

               <?php if ($end_page < $total_pages): ?>
                  <?php if ($end_page < $total_pages - 1): ?>
                     <span class="pagination-ellipsis">...</span>
                  <?php endif; ?>
                  <a href="?page=<?php echo $total_pages; ?>" class="pagination-number"><?php echo $total_pages; ?></a>
               <?php endif; ?>
            </div>

            <?php if ($current_page < $total_pages): ?>
               <a href="?page=<?php echo $current_page + 1; ?>" class="pagination-btn">Next →</a>
            <?php else: ?>
               <span class="pagination-btn disabled">Next →</span>
            <?php endif; ?>
         </div>
      <?php endif; ?>

   </section>

   <!-- packages section ends -->


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