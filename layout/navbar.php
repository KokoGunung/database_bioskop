<header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b">
  <nav class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
    <a href="index.php" class="font-bold text-xl">Bioskop</a>
    <div class="flex items-center gap-4 text-sm">
      <?php if (!empty($_SESSION['user'])): ?>
        <span class="hidden sm:inline text-gray-600">
          Hi, <?= htmlspecialchars($_SESSION['user']['nama'] ?? $_SESSION['user']['email']) ?>
        </span>
        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
          <a href="admin.php" class="text-indigo-600 hover:text-indigo-700">Admin</a>
        <?php endif; ?>
        <a href="profile.php" class="text-indigo-600 hover:text-indigo-700">Profil</a>
        <a href="logout.php" class="text-red-600 hover:text-red-700">Keluar</a>
      <?php else: ?>
        <a href="login.php" class="text-indigo-600 hover:text-indigo-700">Masuk</a>
        <a href="register.php" class="text-indigo-600 hover:text-indigo-700">Daftar</a>
      <?php endif; ?>
    </div>
  </nav>
</header>
