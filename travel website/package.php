<?php
// Include authentication functions
require_once __DIR__ . '/includes/auth.php';
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

   <div class="heading heading-package">
      <h1>packages</h1>
   </div>

   <!-- packages section starts  -->

   <section class="packages">

      <h1 class="heading-title">top destinations</h1>

      <div class="box-container">

         <div class="box">
            <div class="image">
               <img src="images/hall1.jpeg" alt="hallstatt">
            </div>
            <div class="content">
               <h3>Winter Hallstatt</h3>
               <p>Fascinating one-day trip to the most beautiful village in Austria,
                  located near the mountain lake of the same name—Hallstatt.</p>
               <a href="book.php" class="btn">book now</a>
            </div>
         </div>

         <div class="box">
            <div class="image">
               <img src="images/vien1.jpeg" alt="vienna">
            </div>
            <div class="content">
               <h3>Vienna</h3>
               <p>An enchanting one-day adventure to Vienna,
                  immersing ourselves in the rich cultural heritage of this historic city.</p>
               <a href="book.php" class="btn">book now</a>
            </div>
         </div>

         <div class="box">
            <div class="image">
               <img src="images/buda1.jpeg" alt="budapest">
            </div>
            <div class="content">
               <h3>Early spring in Budapest</h3>
               <p>Embarking on an invigorating one-day escapade to Budapest,
                  indulging in the vibrant beauty of spring along the Danube River.</p>
               <a href="book.php" class="btn">book now</a>
            </div>
         </div>

         <div class="box">
            <div class="image">
               <img src="images/barc4.jpeg" alt="barcelona">
            </div>
            <div class="content">
               <h3>Sunny Barcelona</h3>
               <p>Barcelona, a city where history, art and culture converge, awaits your discovery.<br> Get lost in the historic Gothic Quarter and explore the Picasso Museum.
                  <br>Join our three-day journey
               </p>
               <a href="book.php" class="btn">book now</a>
            </div>
         </div>

         <div class="box">
            <div class="image">
               <img src="images/rome5.jpeg" alt="rome">
            </div>
            <div class="content">
               <h3>Italian weekend: Rome</h3>
               <p>Embark on an unforgettable two-day escapade through the city of Rome.
                  <br>Our journey unfolds amidst ancient wonders, quality food and wine and lots of memories to take back.
               </p>
               <a href="book.php" class="btn">book now</a>
            </div>
         </div>

         <div class="box">
            <div class="image">
               <img src="images/tulip6.jpeg" alt="netherlands">
            </div>
            <div class="content">
               <h3>Tulips festival: Netherlands</h3>
               <p>Experience the burst of color and fragrance at the annual Tulip Festival in Amsterdam, where millions of tulips paint the city in a mesmerizing palette each spring.</p>
               <a href="book.php" class="btn">book now</a>
            </div>
         </div>

         <div class="box">
            <div class="image">
               <img src="images/par3.jpeg" alt="paris">
            </div>
            <div class="content">
               <h3>Romantic Paris</h3>
               <p>A Delightful One-Day Escape To Paris,wandering Through Charming Streets, Iconic Landmarks,And Experiencing The Timeless Romance Of The City Of Light</p>
               <a href="book.php" class="btn">book now</a>
            </div>
         </div>

         <div class="box">
            <div class="image">
               <img src="images/prague.jpg" alt="prague">
            </div>
            <div class="content">
               <h3>Magical Prague</h3>
               <p>An Unforgettable Journey To Prague,Discovering Medieval Architecture,The Historic Charles Bridge, And The Heart Of Old Town.</p>
               <a href="book.php" class="btn">book now</a>
            </div>
         </div>

         <div class="box">
            <div class="image">
               <img src="images/lisbon.jpg" alt="lisbon">
            </div>
            <div class="content">
               <h3>Coastal Lisbon</h3>
               <p>A Refreshing weekend Getaway To Lisbon,Enjoying Colorful Streets, Ocean Views,And The Unique Charm Of Portugal's Capital.</p>
               <a href="book.php" class="btn">book now</a>
            </div>
         </div>

         <div class="box">
            <div class="image">
               <img src="images/zurich.jpg" alt="zurich">
            </div>
            <div class="content">
               <h3>Scenic Zurich</h3>
               <p>A Peaceful three-days Experience In Zurich,Blending Alpine Views With Modern Elegance,Historic Old Town Walks, And Lakeside Serenity.</p>
               <a href="book.php" class="btn">book now</a>
            </div>
         </div>

         <div class="box">
            <div class="image">
               <img src="images/yoga.jpg" alt="yoga">
            </div>
            <div class="content">
               <h3>Yoga Retreat: Sri Lanka</h3>
               <p>A Rejuvenating Two-Week Yoga Journey In Sri Lanka,Combining Daily Yoga Practice With Ocean Breezes,Tropical Nature, And Deep Relaxation.</p>
               <a href="book.php" class="btn">book now</a>
            </div>
         </div>

         <div class="box">
            <div class="image">
               <img src="images/jap12.jpg" alt="japan">
            </div>
            <div class="content">
               <h3>Two-Week Journey Through Japan</h3>
               <p>An Extraordinary Two-Week Adventure Across Japan,Exploring The Perfect Harmony Of Ancient Traditions And Modern Innovation, From Tokyo To Kyoto And Beyond.</p>
               <a href="book.php" class="btn">book now</a>
            </div>
         </div>

      </div>

      <div class="load-more"><span class="btn">load more</span></div>

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