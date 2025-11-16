<?php
require "auth.php";
require "service/database.php";
require_login();

function h($x){ return htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8'); }

// Ambil semua pemesanan milik user beserta ringkasan tiketnya
$sql = "
SELECT 
  p.id_pemesanan,
  p.tanggal_pesan,
  p.jumlah_tiket,
  j.id_jadwal,
  j.tanggal AS tgl_tayang,
  j.jam_mulai,
  j.jam_selesai,
  f.judul,
  s.nama_studio,
  COALESCE(GROUP_CONCAT(t.nomor_kursi ORDER BY t.nomor_kursi SEPARATOR ', '), '-') AS kursi,
  COALESCE(SUM(t.harga), 0) AS total_harga
FROM pemesanan p
LEFT JOIN tiket t ON t.id_pemesanan = p.id_pemesanan
LEFT JOIN jadwal_tayang j ON t.id_jadwal = j.id_jadwal
LEFT JOIN film f ON j.id_film = f.id_film
LEFT JOIN studio s ON f.id_studio = s.id_studio
WHERE p.id_penonton = ?
GROUP BY p.id_pemesanan
ORDER BY p.tanggal_pesan DESC
";

$stmt = $db->prepare($sql);
$stmt->bind_param("i", $_SESSION['user']['id']);
$stmt->execute();
$res = $stmt->get_result();
$items = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Pesanan Saya — Bioskop</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <?php include "layout/navbar.php"; ?>

  <main class="max-w-6xl mx-auto px-4 py-8">
    <header class="mb-6">
      <h1 class="text-2xl md:text-3xl font-bold">Pesanan Saya</h1>
      <p class="text-gray-600 mt-1">Riwayat pemesanan tiket milik akunmu.</p>
    </header>

    <?php if (empty($items)): ?>
      <div class="rounded-2xl border bg-white p-6 text-gray-600">Belum ada pemesanan.</div>
    <?php else: ?>
      <div class="space-y-4">
        <?php foreach($items as $it): ?>
          <article class="rounded-2xl border bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
              <div class="space-y-1">
                <div class="text-sm text-gray-500">ID Pemesanan</div>
                <div class="font-semibold"><?= h($it['id_pemesanan']) ?></div>
              </div>
              <div class="text-right text-sm text-gray-500">
                Dipesan: <?= h($it['tanggal_pesan']) ?>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div class="space-y-1">
                <div class="flex justify-between"><span>Film</span><span class="font-medium"><?= h($it['judul'] ?? '-') ?></span></div>
                <div class="flex justify-between"><span>Studio</span><span><?= h($it['nama_studio'] ?? '-') ?></span></div>
                <div class="flex justify-between"><span>Tanggal Tayang</span><span><?= h($it['tgl_tayang'] ?? '-') ?></span></div>
              </div>
              <div class="space-y-1">
                <div class="flex justify-between"><span>Jam</span><span>
                  <?php if (!empty($it['jam_mulai'])): ?>
                    <?= h(substr((string)$it['jam_mulai'],0,5)) ?> - <?= h(substr((string)$it['jam_selesai'],0,5)) ?>
                  <?php else: ?>-<?php endif; ?>
                </span></div>
                <div class="flex justify-between"><span>Kursi</span><span><?= h($it['kursi']) ?></span></div>
                <div class="flex justify-between"><span>Jumlah Tiket</span><span><?= (int)$it['jumlah_tiket'] ?></span></div>
              </div>
            </div>

            <div class="mt-3 flex items-center justify-between">
              <div class="text-sm text-gray-500">ID Jadwal: <?= h($it['id_jadwal'] ?? '-') ?></div>
              <div class="text-base font-semibold">Total: Rp <?= number_format((int)$it['total_harga'], 0, ',', '.') ?></div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <a href="index.php" class="inline-block mt-6 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl">Kembali ke Beranda</a>
  </main>
</body>
</html>
