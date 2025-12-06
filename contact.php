<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-in-out, padding 0.3s ease-in-out; }
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
<body class="bg-gray-50">

    <header id="header" class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="index.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          <nav class="hidden md:flex items-center space-x-8">
            <a href="index.php#gallery-section" class="text-gray-700 hover:text-purple-600 transition-colors">Gallery</a>
            <a href="index.php#features-section" class="text-gray-700 hover:text-purple-600 transition-colors">How It Works</a>
            <a href="about.php" class="text-gray-700 hover:text-purple-600 transition-colors">About</a>
            <a href="contact.php" class="text-purple-600 font-semibold">Contact</a>
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
              <a href="about.php" class="text-gray-700 hover:text-purple-600">About</a>
              <a href="contact.php" class="text-purple-600 font-semibold">Contact</a>
              <div class="flex flex-col space-y-2 pt-3 border-t border-gray-100">
                <a href="login.php" class="text-gray-700 hover:text-purple-600">Login</a>
                <a href="creg.php" class="bg-gradient-to-r from-purple-600 to-pink-500 text-white px-10 py-4 rounded-lg text-lg font-bold transition-all duration-300 transform hover:scale-105 hover:shadow-xl shadow-lg">Register as Customer</a>
                <a href="treg.php" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg text-center mt-2">Tailor Register</a>
              </div>
            </div>
        </div>
      </div>
    </header>

    <main>
        <section class="relative h-[50vh] md:h-[60vh] flex items-center text-white overflow-hidden">
            <div class="absolute inset-0 w-full h-full" id="hero-slideshow-container">
                <div class="hero-slideshow-slide active" style="background-image: url('images/admin-bg.jpg');"></div>
                <div class="hero-slideshow-slide" style="background-image: url('images/custombussiness.jpg');"></div>
                <div class="hero-slideshow-slide" style="background-image: url('images/formalwear.jpg');"></div>
                <div class="hero-slideshow-slide" style="background-image: url('images/ctabgtailor.jpg');"></div>
            </div>
            <div class="absolute inset-0 bg-purple-900 bg-opacity-70 z-10"></div>
            <div class="container mx-auto px-6 relative z-20 text-center">
              <h1 class="text-4xl md:text-6xl font-bold mb-4" data-aos="fade-down">Get In Touch</h1>
              <p class="text-lg md:text-xl text-purple-200 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">Have a question, a suggestion, or need support? We're here to help.</p>
            </div>
        </section>

        <section class="py-20 bg-gray-50">
            <div class="container mx-auto px-6">
                <div class="grid lg:grid-cols-5 gap-12">
                    <div class="lg:col-span-2" data-aos="fade-right">
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Contact Information</h2>
                        <p class="text-gray-600 mb-8">Fill out the form and our team will get back to you within 24 hours. You can also reach us through the channels below.</p>
                        <div class="space-y-6">
                            <div class="flex items-start gap-4 p-4 bg-white rounded-lg shadow-sm">
                                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center shrink-0"><i class="ri-map-pin-2-line text-2xl"></i></div>
                                <div><p class="font-semibold text-gray-800">Our Office</p><p class="text-gray-600">Thiruvananthapuram, Kerala, India</p></div>
                            </div>
                            <div class="flex items-start gap-4 p-4 bg-white rounded-lg shadow-sm">
                                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center shrink-0"><i class="ri-mail-send-line text-2xl"></i></div>
                                <div><p class="font-semibold text-gray-800">Email Us</p><a href="mailto:contact@stitchverse.com" class="text-purple-600 hover:underline">contact@stitchverse.com</a></div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-3 bg-white p-8 md:p-10 rounded-2xl shadow-xl" data-aos="fade-left" data-aos-delay="100">
                        <form action="contact_action.php" method="POST">
                            <div class="grid sm:grid-cols-2 gap-6">
                                <div><label for="name" class="block mb-2 text-sm font-medium text-gray-700">Full Name</label><input type="text" name="name" id="name" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></div>
                                <div><label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email Address</label><input type="email" name="email" id="email" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></div>
                                <div class="sm:col-span-2"><label for="subject" class="block mb-2 text-sm font-medium text-gray-700">Subject</label><input type="text" name="subject" id="subject" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></div>
                                <div class="sm:col-span-2"><label for="message" class="block mb-2 text-sm font-medium text-gray-700">Message</label><textarea name="message" id="message" rows="5" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></textarea></div>
                                <div class="sm:col-span-2"><button type="submit" class="w-full px-8 py-3 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-semibold shadow-lg transform hover:-translate-y-0.5 transition-transform">Send Message</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16" data-aos="fade-up"><h2 class="text-4xl font-bold text-gray-900">Frequently Asked Questions</h2></div>
                <div class="max-w-3xl mx-auto space-y-4">
                    <div class="border border-gray-200 rounded-lg" data-aos="fade-up" data-aos-delay="100"><button class="faq-question w-full flex justify-between items-center text-left p-5 font-semibold text-gray-800"><span>How do I place a custom stitch request?</span><i class="ri-add-line text-xl transition-transform duration-300"></i></button><div class="faq-answer px-5 text-gray-600 leading-relaxed"><p class="pt-2 pb-5">Simply sign up for a customer account, go to the "Stitch Request" page after logging in, and fill out the form with your design details, measurements, and any inspiration photos. Your request will then be visible to our network of talented tailors.</p></div></div>
                    <div class="border border-gray-200 rounded-lg" data-aos="fade-up" data-aos-delay="200"><button class="faq-question w-full flex justify-between items-center text-left p-5 font-semibold text-gray-800"><span>How can I register as a tailor?</span><i class="ri-add-line text-xl transition-transform duration-300"></i></button><div class="faq-answer px-5 text-gray-600 leading-relaxed"><p class="pt-2 pb-5">We'd love to have you! Click the "Tailor Register" button in the header. You will need to fill out an application with details about your experience and portfolio. Our team will review your application and get back to you.</p></div></div>
                    <div class="border border-gray-200 rounded-lg" data-aos="fade-up" data-aos-delay="300"><button class="faq-question w-full flex justify-between items-center text-left p-5 font-semibold text-gray-800"><span>Is my payment secure?</span><i class="ri-add-line text-xl transition-transform duration-300"></i></button><div class="faq-answer px-5 text-gray-600 leading-relaxed"><p class="pt-2 pb-5">Absolutely. All payments are processed through a secure gateway. We hold the payment until you confirm that you have received your order as described, ensuring protection for both customers and tailors.</p></div></div>
                </div>
            </div>
        </section>
    </main>

    <footer id="contact-section" class="bg-gray-900 text-white py-16">
      <div class="container mx-auto px-6" data-aos="fade-up">
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

            // Mobile menu toggle
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
                }, 5000);
            }

            // FAQ accordion
            const faqQuestions = document.querySelectorAll('.faq-question');
            faqQuestions.forEach(button => {
                button.addEventListener('click', () => {
                    const answer = button.nextElementSibling;
                    const icon = button.querySelector('i');
                    const isOpen = answer.style.maxHeight && answer.style.maxHeight !== '0px';

                    // Close all other open answers
                    faqQuestions.forEach(otherButton => {
                        if (otherButton !== button) {
                            otherButton.nextElementSibling.style.maxHeight = '0px';
                            otherButton.querySelector('i').classList.remove('rotate-45');
                        }
                    });

                    // Toggle the clicked answer
                    if (isOpen) {
                        answer.style.maxHeight = '0px';
                        icon.classList.remove('rotate-45');
                    } else {
                        answer.style.maxHeight = answer.scrollHeight + 'px';
                        icon.classList.add('rotate-45');
                    }
                });
            });
        });
    </script>
</body>
</html>
