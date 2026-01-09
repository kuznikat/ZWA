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
   <title>about</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <!-- header section starts  -->

   <section class="header">

      <a href="home.php" class="logo">Lets travel</a>

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

   <div class="heading heading-about">
      <h1>about us</h1>
   </div>

   <!-- about section starts  -->

   <section class="about">

      <div class="image">
         <img src="images/team01.jpeg" alt="team">
      </div>

      <div class="content">
         <h3>why choose us?</h3>
         <p>We are a young team who enjoys traveling around Europe. This project was created 2 years ago, and since then we realized more than 500 tours. We went from tours with 5–8 participants to tours of 2–3 groups with around 30 participants in each group.</p>
         <p>Our goal is to create an affordable, comfortable and exciting travel experience without stress. We carefully plan each trip to ensure you have the best experience possible.</p>
         <div class="icons-container">
            <div class="icons">
               <span class="icon">📍</span>
               <span>top destinations</span>
            </div>
            <div class="icons">
               <span class="icon">💰</span>
               <span>affordable price</span>
            </div>
            <div class="icons">
               <span class="icon">📞</span>
               <span>24/7 guide service</span>
            </div>
         </div>
      </div>

   </section>

   <!-- about section ends -->

   <!-- reviews section starts  -->

   <section class="reviews">

      <h1 class="heading-title"> clients reviews </h1>

      <div class="reviews-slider">

         <div class="slide">
            <div class="stars">
               <span class="star"></span>
               <span class="star"></span>
               <span class="star"></span>
               <span class="star"></span>
               <span class="star"></span>
            </div>
            <p>"Our two-week journey through Japan was incredibly well planned. From the energy of Tokyo to the calm temples of Kyoto, every stop felt unique. The balance between guided tours and free time was perfect, and I learned so much about Japanese culture and traditions."</p>
            <h3>Daniel Kovář</h3>
            <span>traveler</span>
            <img src="images/pic-1.png" alt="">
         </div>

         <div class="slide">
            <div class="stars">
               <span class="star"></span>
               <span class="star"></span>
               <span class="star"></span>
               <span class="star"></span>
            </div>
            <p>"Hallstatt in winter felt like a fairytale. The organization was excellent, the views were breathtaking, and everything went smoothly from start to finish. It was the perfect one-day escape, and I would recommend this trip to anyone visiting Austria."</p>
            <h3>Anna Müller</h3>
            <span>traveler</span>
            <img src="images/pic-2.png" alt="anna">
         </div>

         <div class="slide">
            <div class="stars">
               <span class="star"></span>
               <span class="star"></span>
               <span class="star"></span>
               <span class="star"></span>
               <span class="star"></span>
            </div>
            <p>"The two-week yoga retreat in Sri Lanka was truly life-changing. Practicing yoga by the ocean, enjoying healthy local food, and slowing down in such a peaceful environment helped me completely reset. The instructors were professional and the atmosphere was unforgettable."</p>
            <h3>David Thompson</h3>
            <span>traveler</span>
            <img src="images/pic-3.png" alt="">
         </div>

      </div>

   </section>

   <!-- reviews section ends -->


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