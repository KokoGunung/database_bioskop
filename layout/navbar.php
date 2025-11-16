<header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b">
  <nav class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
    <a href="index.php" class="font-bold text-xl">Bioskop</a>
    <div class="flex items-center gap-4 text-sm">
      <?php if (!empty($_SESSION['user'])): ?>
  <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
    <span class="hidden sm:inline text-gray-600">Hi, Administrator</span>
    <a href="logout.php" class="text-red-600 hover:text-red-700">Keluar</a>
  <?php else: ?>
    <span class="hidden sm:inline text-gray-600">
      Hi, <?= htmlspecialchars($_SESSION['user']['nama'] ?? $_SESSION['user']['email']) ?>
    </span>
    <a href="pesanan_saya.php" class="text-indigo-600 hover:text-indigo-700">Pesanan Saya</a>
    <a href="profile.php" class="text-indigo-600 hover:text-indigo-700">Profil</a>
    <a href="logout.php" class="text-red-600 hover:text-red-700">Keluar</a>
  <?php endif; ?>
<?php else: ?>
  <a href="login.php" class="text-indigo-600 hover:text-indigo-700">Masuk</a>
  <a href="register.php" class="text-indigo-600 hover:text-indigo-700">Daftar</a>
<?php endif; ?>
    </div>
  </nav>
</header>
