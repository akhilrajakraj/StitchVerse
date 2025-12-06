<!-- includes/customer_sidebar.php -->
<aside class="w-64 bg-white dark:bg-gray-800 shadow-lg px-6 py-8 fixed h-full flex flex-col">
  <!-- Branding + Dark mode toggle -->
  <div class="flex items-center justify-between mb-10">
    <a href="index.php" class="text-2xl font-extrabold text-purple-600 dark:text-purple-400">
      🧵 ThreadHub
    </a>
    <button onclick="toggleDarkMode()" title="Toggle Dark Mode"
            class="text-sm px-2 py-1 bg-purple-100 dark:bg-purple-700 text-purple-800 dark:text-white rounded hover:scale-105 transition">
      🌓
    </button>
  </div>

  <!-- Navigation -->
  <nav class="flex flex-col space-y-3 flex-grow">
    <a href="customer_profile.php"
       class="py-2.5 px-4 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-700 font-medium transition">
      👤 My Profile
    </a>
    <a href="my_orders.php"
       class="py-2.5 px-4 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-700 font-medium transition">
      📦 My Orders
    </a>
    <a href="wishlist.php"
       class="py-2.5 px-4 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-700 font-medium transition">
      ❤️ Wishlist
    </a>
    <a href="browse_designs.php"
       class="py-2.5 px-4 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-700 font-medium transition">
      🎨 Browse Designs
    </a>
  </nav>

  <!-- Logout -->
  <div class="mt-auto">
    <a href="logout.php"
       class="block w-full text-center py-2.5 px-4 rounded-lg bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-100 hover:bg-red-200 dark:hover:bg-red-700 font-medium transition">
      🚪 Logout
    </a>
  </div>
</aside>
