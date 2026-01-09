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
   <title>Lets travel</title>

   <!-- CSS file link  -->
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

   <!-- home section starts  -->

   <section class="home">

      <div class="home-slider">

         <div class="slide slide-1">
            <div class="content">
               <span>explore, discover, travel</span>
               <h3>travel around the world</h3>
               <a href="package.php" class="btn">discover more</a>
            </div>
         </div>

         <div class="slide slide-2">
            <div class="content">
               <span>explore, discover, travel</span>
               <h3>discover new places</h3>
               <a href="package.php" class="btn">discover more</a>
            </div>
         </div>

         <div class="slide slide-3">
            <div class="content">
               <span>explore, discover, travel</span>
               <h3>make your tour worthwhile</h3>
               <a href="package.php" class="btn">discover more</a>
            </div>
         </div>

      </div>

   </section>

   <!-- home section ends -->

   <!-- services section starts  -->

   <section class="services">

      <h1 class="heading-title"> our services </h1>

      <div class="box-container">

         <div class="box">
            <img src="images/icon-1.png" alt="mountains">
            <h3>adventure</h3>
         </div>

         <div class="box">
            <img src="images/icon-2.png" alt="map">
            <h3>tour guide</h3>
         </div>

         <div class="box">
            <img src="images/icon-3.png" alt="backpack">
            <h3>trekking</h3>
         </div>

         <div class="box">
            <img src="images/icon-4.png" alt="campfire">
            <h3>camp fire</h3>
         </div>

         <div class="box">
            <img src="images/icon-5.png" alt="off road">
            <h3>off-road</h3>
         </div>

         <div class="box">
            <img src="images/icon-6.png" alt="camp">
            <h3>camping</h3>
         </div>

      </div>

   </section>

   <!-- services section ends -->

   <!-- home about section starts  -->

   <section class="home-about">

      <div class="image">
         <img src="images/team.jpeg" alt="team">
      </div>

      <div class="content">
         <h3>about us</h3>
         <p>We are a young team who enjoys traveling around Europe. This project was created 2 years ago, and since then we realized more than 500 tours. We went from tours with 5–8 participants to tours of 2–3 groups with around 30 participants in each group.</p>
         <p>Our goal is to create an affordable, comfortable and exciting travel experience without stress. We carefully plan each trip to ensure you have the best experience possible.</p>

         <a href="about.php" class="btn">read more</a>
      </div>

   </section>

   <!-- home about section ends -->

   <!-- the home packages section starts  -->

   <section class="home-packages">

      <h1 class="heading-title"> our packages </h1>

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

      </div>

      <div class="load-more"> <a href="package.php" class="btn">load more</a> </div>

   </section>

   <!-- home packages section ends -->


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