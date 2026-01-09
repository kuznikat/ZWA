// Mobile menu toggle
let menu = document.querySelector('#menu-btn');
let navbar = document.querySelector('.header .navbar');

if (menu) {
   menu.onclick = () => {
      menu.classList.toggle('active');
      navbar.classList.toggle('active');
   };
}

window.onscroll = () => {
   if (menu) {
      menu.classList.remove('active');
      navbar.classList.remove('active');
   }
};

// Number input maxlength handler (only for inputs with maxLength attribute)
document.querySelectorAll('input[type="number"][maxlength]').forEach(inputNumber => {
   inputNumber.oninput = () => {
      if (inputNumber.maxLength && inputNumber.value.length > inputNumber.maxLength) {
         inputNumber.value = inputNumber.value.slice(0, inputNumber.maxLength);
      }
   };
});

// Home slider - vanilla JavaScript replacement for Swiper
function initHomeSlider() {
   const slider = document.querySelector('.home-slider');
   if (!slider) return;

   const slides = slider.querySelectorAll('.slide');
   if (slides.length === 0) return;

   let currentSlide = 0;
   let autoSlideInterval;

   // Show first slide
   slides[0].classList.add('active');

   // Check if buttons already exist (to avoid duplicates)
   let nextBtn = slider.querySelector('.slider-button-next');
   let prevBtn = slider.querySelector('.slider-button-prev');

   // Create navigation buttons if they don't exist
   if (!nextBtn) {
      nextBtn = document.createElement('button');
      nextBtn.className = 'slider-button slider-button-next';
      nextBtn.innerHTML = '→';
      nextBtn.setAttribute('aria-label', 'Next slide');
      slider.appendChild(nextBtn);
   }

   if (!prevBtn) {
      prevBtn = document.createElement('button');
      prevBtn.className = 'slider-button slider-button-prev';
      prevBtn.innerHTML = '←';
      prevBtn.setAttribute('aria-label', 'Previous slide');
      slider.appendChild(prevBtn);
   }

   function showSlide(index) {
      // Remove active class from all slides
      slides.forEach(slide => slide.classList.remove('active'));

      // Calculate new slide index
      if (index >= slides.length) {
         currentSlide = 0;
      } else if (index < 0) {
         currentSlide = slides.length - 1;
      } else {
         currentSlide = index;
      }

      // Add active class to current slide
      slides[currentSlide].classList.add('active');
   }

   function nextSlide() {
      showSlide(currentSlide + 1);
      resetAutoSlide();
   }

   function prevSlide() {
      showSlide(currentSlide - 1);
      resetAutoSlide();
   }

   function resetAutoSlide() {
      clearInterval(autoSlideInterval);
      autoSlideInterval = setInterval(nextSlide, 5000);
   }

   // Add event listeners
   nextBtn.addEventListener('click', nextSlide);
   prevBtn.addEventListener('click', prevSlide);

   // Auto-advance slides every 5 seconds
   autoSlideInterval = setInterval(nextSlide, 5000);
}

// Reviews slider - vanilla JavaScript replacement for Swiper
function initReviewsSlider() {
   const slider = document.querySelector('.reviews-slider');
   if (!slider) return;

   const slides = slider.querySelectorAll('.slide');
   if (slides.length === 0) return;

   let currentIndex = 0;
   const slidesPerView = getSlidesPerView();

   function getSlidesPerView() {
      if (window.innerWidth >= 1000) return 3;
      if (window.innerWidth >= 700) return 2;
      return 1;
   }

   function updateSlider() {
      const view = getSlidesPerView();
      slides.forEach((slide, index) => {
         slide.style.display = 'none';
         if (index >= currentIndex && index < currentIndex + view) {
            slide.style.display = 'block';
         }
      });
   }

   function nextSlide() {
      const view = getSlidesPerView();
      if (currentIndex + view < slides.length) {
         currentIndex++;
      } else {
         currentIndex = 0; // Loop back
      }
      updateSlider();
   }

   function prevSlide() {
      const view = getSlidesPerView();
      if (currentIndex > 0) {
         currentIndex--;
      } else {
         currentIndex = Math.max(0, slides.length - view); // Loop to end
      }
      updateSlider();
   }

   // Auto-advance
   setInterval(nextSlide, 4000);

   // Initialize
   updateSlider();

   // Handle window resize
   let resizeTimer;
   window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
         updateSlider();
      }, 250);
   });
}

// Load more packages (client-side - will be replaced with server-side pagination later)
let loadMoreBtn = document.querySelector('.packages .load-more .btn');
if (loadMoreBtn) {
   let currentItem = 3;

   loadMoreBtn.onclick = () => {
      let boxes = [...document.querySelectorAll('.packages .box-container .box')];
      for (var i = currentItem; i < currentItem + 3; i++) {
         if (boxes[i]) {
            boxes[i].style.display = 'inline-block';
         }
      }
      currentItem += 3;
      if (currentItem >= boxes.length) {
         loadMoreBtn.style.display = 'none';
      }
   };
}

// Initialize sliders when DOM is ready
if (document.readyState === 'loading') {
   document.addEventListener('DOMContentLoaded', () => {
      initHomeSlider();
      initReviewsSlider();
   });
} else {
   initHomeSlider();
   initReviewsSlider();
}
