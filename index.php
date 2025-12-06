<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StitchVerse - Custom Tailoring Reimagined</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        
        /* --- New Hero Slideshow Styles --- */
        #hero-slideshow-container {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            overflow: hidden;
        }
        .hero-slide {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
            transform: scale(1.1); /* Start slightly zoomed in */
        }
        .hero-slide.active {
            opacity: 1;
            z-index: 1;
        }
        .hero-slide.ken-burns-active {
            animation: kenBurns 8s linear forwards;
        }
        @keyframes kenBurns {
            0% {
                transform: scale(1.0) rotate(0.01deg); /* rotate fixes a slight flicker in some browsers */
            }
            100% {
                transform: scale(1.15) rotate(0.01deg);
            }
        }
        /* --- End New Styles --- */
    </style>
</head>
<body class="bg-white">

    <header id="header" class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="index.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          <nav class="hidden md:flex items-center space-x-8">
            <a href="#gallery-section" class="text-gray-700 hover:text-purple-600 transition-colors">Gallery</a>
            <a href="#features-section" class="text-gray-700 hover:text-purple-600 transition-colors">How It Works</a>
            <a href="about.php" class="text-gray-700 hover:text-purple-600 transition-colors">About</a>
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
              <a href="#gallery-section" class="text-gray-700 hover:text-purple-600">Gallery</a>
              <a href="#features-section" class="text-gray-700 hover:text-purple-600">How It Works</a>
              <a href="about.php" class="text-gray-700 hover:text-purple-600">About</a>
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
        <section id="hero-section" class="relative min-h-screen flex items-center">
          <div id="hero-slideshow-container">
            </div>
          <div class="absolute inset-0 bg-black/50 z-10"></div>
          <div class="container mx-auto px-6 relative z-20">
            <div id="hero-text-container" class="max-w-3xl text-white">
              </div>
          </div>
        </section>
        <section id="features-section" class="py-20 bg-gray-50">
          <div class="container mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
              <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Why Choose StitchVerse?</h2>
              <p class="text-xl text-gray-600 max-w-3xl mx-auto">We've revolutionized custom tailoring by connecting customers with skilled artisans, making professional custom clothing accessible to everyone.</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
              <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 group" data-aos="fade-up" data-aos-delay="100">
                  <div class="w-16 h-16 flex items-center justify-center bg-purple-100 text-purple-600 rounded-lg mb-6 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300"><i class="ri-scissors-line text-2xl"></i></div>
                  <h3 class="text-xl font-semibold text-gray-900 mb-3">Expert Tailors</h3>
                  <p class="text-gray-600 leading-relaxed">Connect with verified professional tailors from around the world, each with proven expertise.</p>
              </div>
              <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 group" data-aos="fade-up" data-aos-delay="200">
                  <div class="w-16 h-16 flex items-center justify-center bg-purple-100 text-purple-600 rounded-lg mb-6 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300"><i class="ri-smartphone-line text-2xl"></i></div>
                  <h3 class="text-xl font-semibold text-gray-900 mb-3">Easy Ordering</h3>
                  <p class="text-gray-600 leading-relaxed">A simple online process to upload designs, specify measurements, and place custom orders.</p>
              </div>
              <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 group" data-aos="fade-up" data-aos-delay="300">
                  <div class="w-16 h-16 flex items-center justify-center bg-purple-100 text-purple-600 rounded-lg mb-6 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300"><i class="ri-shield-check-line text-2xl"></i></div>
                  <h3 class="text-xl font-semibold text-gray-900 mb-3">Quality Guarantee</h3>
                  <p class="text-gray-600 leading-relaxed">Every order comes with quality assurance, secure payments, and customer protection.</p>
              </div>
            </div>
          </div>
        </section>

        <section id="gallery-section" class="py-20 bg-white">
          <div class="container mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
              <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Inspiration Gallery</h2>
              <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-12">Discover stunning custom pieces created by our talented tailors. See what's possible when craftsmanship meets creativity.</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
              <div class="group transform hover:scale-105 transition-all duration-300" data-aos="zoom-in-up" data-aos-delay="100">
                  <div class="relative overflow-hidden rounded-xl bg-gray-100 aspect-[4/5] mb-4">
                      <img src="images/bridalgown.jpg" alt="Elegant Wedding Gown" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                  </div>
                  <h3 class="text-xl font-semibold text-gray-900 group-hover:text-purple-600">Elegant Wedding Gown</h3>
              </div>
              <div class="group transform hover:scale-105 transition-all duration-300" data-aos="zoom-in-up" data-aos-delay="200">
                  <div class="relative overflow-hidden rounded-xl bg-gray-100 aspect-[4/5] mb-4">
                      <img src="images/custombussiness.jpg" alt="Custom Business Suit" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                  </div>
                  <h3 class="text-xl font-semibold text-gray-900 group-hover:text-purple-600">Custom Business Suit</h3>
              </div>
              <div class="group transform hover:scale-105 transition-all duration-300" data-aos="zoom-in-up" data-aos-delay="300">
                  <div class="relative overflow-hidden rounded-xl bg-gray-100 aspect-[4/5] mb-4">
                      <img src="images/summerfloaral.jpg" alt="Summer Floral Dress" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                  </div>
                  <h3 class="text-xl font-semibold text-gray-900 group-hover:text-purple-600">Summer Floral Dress</h3>
              </div>
            </div>
          </div>
        </section>

        <section id="about-section" class="py-20 bg-gray-50">
          <div class="container mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
              <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Explore Design Possibilities</h2>
              <p class="text-xl text-gray-600 max-w-3xl mx-auto">From timeless classics to modern trends, our tailors can craft any style you can imagine. Get inspired by what we can create for you.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="relative rounded-xl overflow-hidden group aspect-[3/4]" data-aos="fade-up" data-aos-delay="100"><img src="images/formalwear.jpg" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy" alt="Formal Wear"><div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div><div class="absolute bottom-0 left-0 p-6"><h3 class="text-2xl font-semibold text-white">Formal Wear</h3></div></div>
                <div class="relative rounded-xl overflow-hidden group aspect-[3/4]" data-aos="fade-up" data-aos-delay="200"><img src="images/eveningdress.jpg" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy" alt="Evening Dresses"><div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div><div class="absolute bottom-0 left-0 p-6"><h3 class="text-2xl font-semibold text-white">Evening Dresses</h3></div></div>
                <div class="relative rounded-xl overflow-hidden group aspect-[3/4]" data-aos="fade-up" data-aos-delay="300"><img src="images/uniforms.jpg" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy" alt="Professional Uniforms"><div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div><div class="absolute bottom-0 left-0 p-6"><h3 class="text-2xl font-semibold text-white">Uniforms</h3></div></div>
                <div class="relative rounded-xl overflow-hidden group aspect-[3/4]" data-aos="fade-up" data-aos-delay="400"><img src="images/bridalgown.jpg" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy" alt="Bridal Gowns"><div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div><div class="absolute bottom-0 left-0 p-6"><h3 class="text-2xl font-semibold text-white">Bridal Gowns</h3></div></div>
            </div>
          </div>
        </section>

        <section class="py-20 bg-purple-700">
            <div class="container mx-auto px-6 text-center" data-aos="zoom-in">
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
          <div><h4 class="text-lg font-semibold mb-4">For Customers</h4><ul class="space-y-2"><li><a href="#gallery-section" class="text-gray-400 hover:text-white">Browse Gallery</a></li><li><a href="#" class="text-gray-400 hover:text-white">Find Tailors</a></li><li><a href="login.php" class="text-gray-400 hover:text-white">Place an Order</a></li></ul></div>
          <div><h4 class="text-lg font-semibold mb-4">For Tailors</h4><ul class="space-y-2"><li><a href="treg.php" class="text-gray-400 hover:text-white">Join Platform</a></li><li><a href="login.php" class="text-gray-400 hover:text-white">Dashboard Login</a></li><li><a href="#" class="text-gray-400 hover:text-white">Support</a></li></ul></div>
          <div><h4 class="text-lg font-semibold mb-4">Contact Info</h4><p class="text-gray-400">Thiruvananthapuram, Kerala, India</p><p class="text-gray-400">contact@stitchverse.com</p></div>
        </div>
        <div class="border-t border-gray-800 mt-12 pt-8 text-center"><p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p></div>
      </div>
    </footer>
    
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
            });

            const menuBtn = document.getElementById('menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            menuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });

            // --- Upgraded Hero Slider Logic ---
            const slideContainer = document.getElementById('hero-slideshow-container');
            const textContainer = document.getElementById('hero-text-container');
            if (slideContainer && textContainer) {
                const slides = [
                    { image: 'images/backgroundindex.jpg', title: "Custom Tailoring Made Simple", subtitle: "Connect with skilled tailors worldwide for perfectly fitted clothing." },
                    { image: 'images/ctabg.jpg', title: "Professional Tailors at Your Service", subtitle: "From wedding dresses to business suits - find the perfect tailor for your needs." },
                    { image: 'images/tailorbghome.jpg', title: "Your Style, Perfectly Crafted", subtitle: "Upload your design, set your measurements, and watch your vision come to life." }
                ];
                let currentSlide = 0;
                let slideElements = [];

                // Create slide divs dynamically
                slides.forEach((slide, index) => {
                    const div = document.createElement('div');
                    div.className = 'hero-slide';
                    div.style.backgroundImage = `url('${slide.image}')`;
                    slideContainer.appendChild(div);
                    slideElements.push(div);
                });

                function updateSlide() {
                    const oldSlide = currentSlide;
                    currentSlide = (currentSlide + 1) % slides.length;
                    
                    const slide = slides[currentSlide];

                    // Update Image
                    slideElements[oldSlide].classList.remove('active', 'ken-burns-active');
                    slideElements[currentSlide].classList.add('active');
                    
                    // Update Text with animation
                    textContainer.style.opacity = 0;
                    setTimeout(() => {
                        textContainer.innerHTML = `
                            <h1 class="text-5xl md:text-7xl font-bold mb-6">${slide.title}</h1>
                            <p class="text-xl md:text-2xl mb-8 text-gray-200 leading-relaxed">${slide.subtitle}</p>
                            <a href="creg.php" class="bg-purple-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-purple-700 transition-all duration-300 transform hover:scale-105">Get Started</a>
                        `;
                        textContainer.style.opacity = 1;
                        // Add Ken Burns effect shortly after the slide becomes active
                        setTimeout(() => slideElements[currentSlide].classList.add('ken-burns-active'), 100);
                    }, 750); // half of the fade transition
                }
                
                // Set initial slide
                textContainer.style.transition = 'opacity 0.75s ease-in-out';
                slideElements[0].classList.add('active');
                setTimeout(() => slideElements[0].classList.add('ken-burns-active'), 100);
                updateSlide(); // Call once to set initial text
                
                // Set interval for changing slides
                setInterval(updateSlide, 7000); // 7 seconds per slide
            }
        });
    </script>
</body>
</html>