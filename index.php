<?php
require __DIR__.'/auth.php';
require __DIR__.'/service/database.php';
require_login();

function h($x){return htmlspecialchars($x,ENT_QUOTES,'UTF-8');}

$qNow = $db->query("
  SELECT f.*, s.nama_studio
  FROM film f
  LEFT JOIN studio s ON s.id_studio = f.id_studio
  WHERE CURDATE() BETWEEN f.mulai_tayang AND f.selesai_tayang
  ORDER BY f.judul
");

$qUp = $db->query("
  SELECT f.*, s.nama_studio
  FROM film f
  LEFT JOIN studio s ON s.id_studio = f.id_studio
  WHERE f.mulai_tayang > CURDATE()
  ORDER BY f.mulai_tayang ASC
");

$qPast = $db->query("
  SELECT f.*, s.nama_studio
  FROM film f
  LEFT JOIN studio s ON s.id_studio = f.id_studio
  WHERE f.selesai_tayang < CURDATE()
  ORDER BY f.selesai_tayang DESC
");
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard — Bioskop</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <?php include "layout/navbar.php"; ?>

  <main class="max-w-6xl mx-auto px-4 py-8">
    <header class="flex items-center justify-between mb-6">
      <h1 class="text-2xl md:text-3xl font-bold">Daftar Film</h1>
    </header>

    <section class="mb-10">
      <h2 class="text-xl md:text-2xl font-bold mb-3">Sedang Tayang</h2>
      <?php if ($qNow->num_rows===0): ?>
        <div class="rounded-xl border bg-white p-4 text-gray-600">Belum ada film.</div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php while($f=$qNow->fetch_assoc()): ?>
            <article class="rounded-2xl border bg-white shadow-sm overflow-hidden">
              <div class="h-40 bg-gradient-to-br from-gray-200 to-gray-100 grid place-items-center"><span class="text-5xl">🎬</span></div>
              <div class="p-4 space-y-2">
                <div class="flex items-start justify-between gap-3">
                  <h3 class="font-semibold text-lg"><?= h($f['judul']) ?></h3>
                  <span class="text-xs px-2 py-1 rounded-full bg-gray-100 border"><?= h($f['genre']) ?></span>
                </div>
                <p class="text-xs text-gray-500">
                  Durasi: <?= h(substr((string)$f['durasi'],0,5)) ?> • <?= (int)$f['rating'] ?>+
                  <?php if(!empty($f['nama_studio'])): ?> • Studio: <?= h($f['nama_studio']) ?><?php endif; ?>
                </p>
                <a href="order.php?id=<?= h($f['id_film']) ?>" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium">Pesan →</a>
              </div>
            </article>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </section>

    <section class="mb-10">
      <h2 class="text-xl md:text-2xl font-bold mb-3">Akan Tayang</h2>
      <?php if ($qUp->num_rows===0): ?>
        <div class="rounded-xl border bg-white p-4 text-gray-600">Tidak ada jadwal mendatang.</div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php while($f=$qUp->fetch_assoc()): ?>
            <article class="rounded-2xl border bg-white shadow-sm overflow-hidden">
              <div class="h-40 bg-gradient-to-br from-gray-200 to-gray-100 grid place-items-center"><span class="text-5xl">🎬</span></div>
              <div class="p-4 space-y-2">
                <div class="flex items-start justify-between gap-3">
                  <h3 class="font-semibold text-lg"><?= h($f['judul']) ?></h3>
                  <span class="text-xs px-2 py-1 rounded-full bg-gray-100 border"><?= h($f['genre']) ?></span>
                </div>
                <p class="text-xs text-gray-500">Mulai <?= h($f['mulai_tayang']) ?></p>
                <a href="order.php?id=<?= h($f['id_film']) ?>" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium">Lihat Jadwal →</a>
              </div>
            </article>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </section>

    <section>
      <h2 class="text-xl md:text-2xl font-bold mb-3">Sudah Pernah Tayang</h2>
      <?php if ($qPast->num_rows===0): ?>
        <div class="rounded-xl border bg-white p-4 text-gray-600">Belum ada arsip.</div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php while($f=$qPast->fetch_assoc()): ?>
            <article class="rounded-2xl border bg-white shadow-sm overflow-hidden opacity-75">
              <div class="h-40 bg-gradient-to-br from-gray-200 to-gray-100 grid place-items-center"><span class="text-5xl">🎬</span></div>
              <div class="p-4 space-y-2">
                <div class="flex items-start justify-between gap-3">
                  <h3 class="font-semibold text-lg"><?= h($f['judul']) ?></h3>
                  <span class="text-xs px-2 py-1 rounded-full bg-gray-100 border"><?= h($f['genre']) ?></span>
                </div>
                <p class="text-xs text-gray-500">Selesai <?= h($f['selesai_tayang']) ?></p>
                <span class="inline-flex items-center gap-2 text-gray-400 font-medium">Tidak tersedia</span>
              </div>
            </article>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
  <?php include "layout/footer.html" ?>


</body>
</html>
