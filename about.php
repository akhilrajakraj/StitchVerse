<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        /* Slideshow Styles */
        .hero-slideshow-slide {
            position: absolute;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
            z-index: 1;
        }
        .hero-slideshow-slide.active {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-white">

    <header id="header" class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="index.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          
          <nav class="hidden md:flex items-center space-x-8">
            <a href="index.php#gallery-section" class="text-gray-700 hover:text-purple-600 transition-colors">Gallery</a>
            <a href="index.php#features-section" class="text-gray-700 hover:text-purple-600 transition-colors">How It Works</a>
            <a href="about.php" class="text-purple-600 font-semibold">About</a>
            <a href="contact.php" class="text-gray-700 hover:text-purple-600 transition-colors">Contact</a>
          </nav>

          <div class="hidden md:flex items-center space-x-4">
            <a href="login.php" class="text-gray-700 hover:text-purple-600 transition-colors">Login</a>
            <a href="creg.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors text-sm">Customer Register</a>
            <a href="treg.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors text-sm">Tailor Register</a>
          </div>

          <button id="menu-btn" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
        </div>

        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-100">
            <div class="flex flex-col space-y-3 mt-4">
              <a href="index.php#gallery-section" class="text-gray-700 hover:text-purple-600">Gallery</a>
              <a href="index.php#features-section" class="text-gray-700 hover:text-purple-600">How It Works</a>
              <a href="about.php" class="text-purple-600 font-semibold">About</a>
              <a href="contact.php" class="text-gray-700 hover:text-purple-600">Contact</a>
              <div class="flex flex-col space-y-2 pt-3 border-t border-gray-100">
                <a href="login.php" class="text-gray-700 hover:text-purple-600">Login</a>
                <a href="creg.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg text-center">Customer Register</a>
                <a href="treg.php" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg text-center mt-2">Tailor Register</a>
              </div>
            </div>
        </div>
      </div>
    </header>

    <main>
        <section class="relative h-[60vh] md:h-[70vh] flex items-center text-white overflow-hidden">
            <div class="absolute inset-0 w-full h-full" id="hero-slideshow-container">
                <div class="hero-slideshow-slide active" style="background-image: url('images/backgroundindex.jpg');"></div>
                <div class="hero-slideshow-slide" style="background-image: url('images/customerhome.jpg.jpg');"></div>
                <div class="hero-slideshow-slide" style="background-image: url('images/tailorbghome.jpg');"></div>
                <div class="hero-slideshow-slide" style="background-image: url('images/ctabg.jpg');"></div>
            </div>
            <div class="absolute inset-0 bg-purple-900 bg-opacity-60 z-10"></div>
            <div class="container mx-auto px-6 relative z-20 text-center">
              <h1 class="text-4xl md:text-6xl font-bold mb-4" data-aos="fade-down" data-aos-duration="1000">Weaving Dreams into Reality</h1>
              <p class="text-lg md:text-xl text-purple-200 max-w-3xl mx-auto" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">StitchVerse is where personal style meets master craftsmanship. We are a global community dedicated to creating clothing that is as unique as you are.</p>
            </div>
        </section>

        <section class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div class="relative" data-aos="fade-right">
                        <img src="images/cstorybride.jpg" alt="A happy customer in a custom wedding gown" class="rounded-2xl shadow-xl w-full h-full object-cover">
                    </div>
                    <div data-aos="fade-left" data-aos-delay="100">
                        <h2 class="text-4xl font-bold text-gray-900 mb-6">Our Mission</h2>
                        <p class="text-lg text-gray-600 leading-relaxed mb-4">Our mission is twofold: to empower talented tailors and artisans by giving them a global platform to showcase their skills, and to provide customers with access to perfectly fitting, custom-made clothing that celebrates their individuality.</p>
                        <p class="text-lg text-gray-600 leading-relaxed">We believe in a world where fashion is not mass-produced, but is a personal expression. We are breaking down the barriers between the creators and the wearers, fostering a community built on quality, trust, and creativity.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="py-20 bg-gray-50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16" data-aos="fade-up">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">The StitchVerse Journey</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">A seamless process from concept to creation, designed for everyone.</p>
                </div>
                <div class="grid md:grid-cols-3 gap-8 text-center">
                    <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="100">
                        <div class="mx-auto mb-6 w-20 h-20 flex items-center justify-center rounded-full bg-pink-100"><i class="ri-lightbulb-flash-line text-4xl text-pink-600"></i></div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">1. Share Your Vision</h3>
                        <p class="text-gray-600">Customers describe their dream outfit, upload inspirations, and provide measurements to start their project.</p>
                    </div>
                    <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="200">
                        <div class="mx-auto mb-6 w-20 h-20 flex items-center justify-center rounded-full bg-purple-100"><i class="ri-shake-hands-line text-4xl text-purple-600"></i></div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">2. Connect & Collaborate</h3>
                        <p class="text-gray-600">Verified tailors review requests, provide quotes, and collaborate with customers to perfect the design details.</p>
                    </div>
                    <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="300">
                        <div class="mx-auto mb-6 w-20 h-20 flex items-center justify-center rounded-full bg-green-100"><i class="ri-rocket-2-line text-4xl text-green-600"></i></div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">3. Create & Deliver</h3>
                        <p class="text-gray-600">The artisan brings the design to life. The final, beautifully crafted garment is shipped directly to the customer's door.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 bg-purple-700" data-aos="zoom-in">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">Ready to Create Something Amazing?</h2>
                <p class="text-xl text-purple-200 mb-10 max-w-3xl mx-auto">Join as a customer to find the perfect tailor, or as a tailor to grow your business.</p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                    <a href="creg.php" class="bg-white text-purple-600 px-10 py-4 rounded-lg text-lg font-bold hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">Register as Customer</a>
                    <a href="treg.php" class="border-2 border-white text-white px-10 py-4 rounded-lg text-lg font-bold hover:bg-white hover:text-purple-600 transition-all duration-300">Register as Tailor</a>
                </div>
            </div>
        </section>
    </main>

    <footer id="contact-section" class="bg-gray-900 text-white py-16">
      <div class="container mx-auto px-6" data-aos="fade-in">
        <div class="grid md:grid-cols-4 gap-8">
          <div>
            <a href="index.php" class="text-2xl font-bold text-purple-400 mb-4 block font-pacifico">StitchVerse</a>
            <p class="text-gray-400 mb-4">Connecting talented tailors with customers worldwide for custom clothing that fits perfectly.</p>
             <div class="flex space-x-4">
               <a href="#" class="w-8 h-8 flex items-center justify-center hover:text-purple-400"><i class="ri-facebook-fill text-xl"></i></a>
               <a href="#" class="w-8 h-8 flex items-center justify-center hover:text-purple-400"><i class="ri-twitter-x-fill text-xl"></i></a>
               <a href="#" class="w-8 h-8 flex items-center justify-center hover:text-purple-400"><i class="ri-instagram-fill text-xl"></i></a>
             </div>
          </div>
          <div><h4 class="text-lg font-semibold mb-4">For Customers</h4><ul class="space-y-2"><li><a href="index.php#gallery-section" class="text-gray-400 hover:text-white">Browse Gallery</a></li><li><a href="login.php" class="text-gray-400 hover:text-white">Place an Order</a></li></ul></div>
          <div><h4 class="text-lg font-semibold mb-4">For Tailors</h4><ul class="space-y-2"><li><a href="treg.php" class="text-gray-400 hover:text-white">Join Platform</a></li><li><a href="login.php" class="text-gray-400 hover:text-white">Dashboard Login</a></li></ul></div>
          <div><h4 class="text-lg font-semibold mb-4">Contact Info</h4><p class="text-gray-400">Thiruvananthapuram, Kerala, India</p><p class="text-gray-400">contact@stitchverse.com</p></div>
        </div>
        <div class="border-t border-gray-800 mt-12 pt-8 text-center"><p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p></div>
      </div>
    </footer>
    
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize AOS
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
            });

            // Mobile Menu Toggle
            document.getElementById('menu-btn').addEventListener('click', function() {
                document.getElementById('mobile-menu').classList.toggle('hidden');
            });

            // Hero Slideshow Logic
            const slides = document.querySelectorAll('.hero-slideshow-slide');
            if (slides.length > 0) {
                let currentSlide = 0;
                setInterval(() => {
                    slides[currentSlide].classList.remove('active');
                    currentSlide = (currentSlide + 1) % slides.length;
                    slides[currentSlide].classList.add('active');
                }, 5000); // Change slide every 5 seconds
            }
        });
    </script>
</body>
</html>
