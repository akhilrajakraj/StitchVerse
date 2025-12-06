<aside class="w-64 bg-white dark:bg-gray-800 shadow-lg px-6 py-8 fixed h-full flex flex-col">
        <div class="flex items-center justify-between mb-10">
            <a href="dashboard.php" class="text-2xl font-extrabold text-purple-600 dark:text-purple-400">🧵 ThreadHub</a>
            <button onclick="toggleDarkMode()" title="Toggle Dark Mode" class="text-sm px-2 py-1 bg-purple-100 dark:bg-purple-700 text-purple-800 dark:text-white rounded hover:scale-105 transition">🌓</button>
        </div>
        <nav class="flex flex-col space-y-3 flex-grow">
            <a href="dashboard.php" class="py-2.5 px-4 rounded-lg font-medium transition <?php echo isActive('dashboard.php') ? 'bg-purple-600 text-white shadow-sm' : 'hover:bg-purple-100 dark:hover:bg-purple-700'; ?>">🧑‍💻 Dashboard</a>
            <a href="upload_design.php" class="py-2.5 px-4 rounded-lg font-medium transition <?php echo isActive('upload_design.php') ? 'bg-purple-600 text-white shadow-sm' : 'hover:bg-purple-100 dark:hover:bg-purple-700'; ?>">📤 Upload Design</a>
            <a href="my_designs.php" class="py-2.5 px-4 rounded-lg font-medium transition <?php echo isActive('my_designs.php') ? 'bg-purple-600 text-white shadow-sm' : 'hover:bg-purple-100 dark:hover:bg-purple-700'; ?>">🎨 My Designs</a>
            <a href="orders.php" class="py-2.5 px-4 rounded-lg font-medium transition <?php echo isActive('orders.php') ? 'bg-purple-600 text-white shadow-sm' : 'hover:bg-purple-100 dark:hover:bg-purple-700'; ?>">📦 View Orders</a>
            <a href="profile.php" class="py-2.5 px-4 rounded-lg font-medium transition <?php echo isActive('profile.php') ? 'bg-purple-600 text-white shadow-sm' : 'hover:bg-purple-100 dark:hover:bg-purple-700'; ?>">👤 Profile</a>
            <a href="../index.php" class="py-2.5 px-4 rounded-lg font-medium transition hover:bg-purple-100 dark:hover:bg-purple-700">🏠 Back to Site</a>
        </nav>
        <div class="mt-auto">
            <a href="../logout.php" class="block w-full text-center py-2.5 px-4 rounded-lg bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-100 hover:bg-red-200 dark:hover:bg-red-700 font-medium transition">🚪 Logout</a>
        </div>
    </aside>