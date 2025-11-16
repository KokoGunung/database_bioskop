<?php
require "auth.php";
require "service/database.php";
require_login();

function h($x){ return htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8'); }

$success = '';
$error   = '';

// ====== HANDLE CANCEL (POST) ======
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['cancel_order'])) {
  $idPsn = trim($_POST['id_pemesanan'] ?? '');
  if ($idPsn === '') {
    $error = 'ID pemesanan tidak valid.';
  } else {
    // Pastikan pesanan milik user
    $q = $db->prepare("SELECT id_pemesanan FROM pemesanan WHERE id_pemesanan=? AND id_penonton=? LIMIT 1");
    $q->bind_param("si", $idPsn, $_SESSION['user']['id']);
    $q->execute();
    $own = $q->get_result()->fetch_assoc();
    $q->close();

    if (!$own) {
      $error = 'Pemesanan tidak ditemukan atau bukan milik Anda.';
    } else {
      // Cek status pembayaran terakhir
      $q = $db->prepare("SELECT status_bayar FROM pembayaran WHERE id_pemesanan=? ORDER BY id_pembayaran DESC LIMIT 1");
      $q->bind_param("s", $idPsn);
      $q->execute();
      $pay = $q->get_result()->fetch_assoc();
      $q->close();

      if (!empty($pay) && ($pay['status_bayar'] ?? '') === 'SUKSES') {
        $error = 'Pemesanan sudah dibayar dan tidak bisa dibatalkan.';
      } else {
        // Jalankan pembatalan dalam transaksi
        try {
          $db->begin_transaction();

          // Hapus tiket (kursi akan bebas kembali)
          $st = $db->prepare("DELETE FROM tiket WHERE id_pemesanan=?");
          $st->bind_param("s", $idPsn);
          $st->execute();
          $st->close();

          // Hapus pembayaran apa pun (jika ada & bukan sukses)
          $st = $db->prepare("DELETE FROM pembayaran WHERE id_pemesanan=?");
          $st->bind_param("s", $idPsn);
          $st->execute();
          $st->close();

          // Hapus pemesanan
          $st = $db->prepare("DELETE FROM pemesanan WHERE id_pemesanan=? AND id_penonton=?");
          $st->bind_param("si", $idPsn, $_SESSION['user']['id']);
          $st->execute();
          $st->close();

          $db->commit();
          $success = 'Pemesanan berhasil dibatalkan dan dihapus.';
        } catch (Throwable $e) {
          $db->rollback();
          $error = 'Gagal membatalkan pesanan. Silakan coba lagi.';
        }
      }
    }
  }
}

/*
  Ambil ringkasan pesanan.
  - id_jadwal diambil dari tiket pertama per pemesanan (MIN(id_jadwal)) agar tetap tampil
    walau tabel 'pemesanan' tidak punya kolom id_jadwal.
*/
$sql = "
SELECT 
  p.id_pemesanan,
  p.tanggal_pesan,
  p.jumlah_tiket,
  j.id_jadwal,                         
  j.tanggal AS tgl_tayang,
  j.jam_mulai, j.jam_selesai,
  f.judul, s.nama_studio,
  COALESCE(GROUP_CONCAT(t.nomor_kursi ORDER BY t.nomor_kursi SEPARATOR ', '), '-') AS kursi,
  COALESCE(SUM(t.harga), 0) AS total_harga,
  COUNT(t.nomor_kursi) AS jumlah_kursi,
  (SELECT pay.metode_bayar FROM pembayaran pay WHERE pay.id_pemesanan=p.id_pemesanan ORDER BY pay.id_pembayaran DESC LIMIT 1) AS metode_bayar,
  (SELECT pay.status_bayar  FROM pembayaran pay WHERE pay.id_pemesanan=p.id_pemesanan ORDER BY pay.id_pembayaran DESC LIMIT 1) AS status_bayar,
  (SELECT pay.total_bayar  FROM pembayaran pay WHERE pay.id_pemesanan=p.id_pemesanan ORDER BY pay.id_pembayaran DESC LIMIT 1) AS total_bayar
FROM pemesanan p
LEFT JOIN (
  SELECT id_pemesanan, MIN(id_jadwal) AS id_jadwal
  FROM tiket
  GROUP BY id_pemesanan
) tj ON tj.id_pemesanan = p.id_pemesanan
LEFT JOIN jadwal_tayang j ON j.id_jadwal = tj.id_jadwal
LEFT JOIN film f ON f.id_film = j.id_film
LEFT JOIN studio s ON s.id_studio = f.id_studio
LEFT JOIN tiket t ON t.id_pemesanan = p.id_pemesanan
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

    <?php if ($success): ?>
      <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="alert">
        <?= h($success) ?>
      </div>
    <?php elseif ($error): ?>
      <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <?= h($error) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
      <div class="rounded-2xl border bg-white p-6 text-gray-600">Belum ada pemesanan.</div>
    <?php else: ?>
      <div class="space-y-4">
        <?php foreach($items as $it): ?>
          <?php
            $displayTotal     = isset($it['total_bayar']) && $it['total_bayar'] !== null ? (int)$it['total_bayar'] : (int)$it['total_harga'];
            $sudahBayarSukses = (isset($it['status_bayar']) && $it['status_bayar'] === 'SUKSES');
            $sudahAdaTiket    = ((int)$it['jumlah_kursi'] > 0); // pakai COUNT kursi
          ?>
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
                <div class="flex justify-between">
                  <span>Film</span><span class="font-medium"><?= h($it['judul'] ?? '-') ?></span>
                </div>
                <div class="flex justify-between">
                  <span>Studio</span><span><?= h($it['nama_studio'] ?? '-') ?></span>
                </div>
                <div class="flex justify-between">
                  <span>Tanggal Tayang</span><span><?= h($it['tgl_tayang'] ?? '-') ?></span>
                </div>
              </div>
              <div class="space-y-1">
                <div class="flex justify-between">
                  <span>Jam</span>
                  <span>
                    <?php if (!empty($it['jam_mulai'])): ?>
                      <?= h(substr((string)$it['jam_mulai'],0,5)) ?> - <?= h(substr((string)$it['jam_selesai'],0,5)) ?>
                    <?php else: ?>-<?php endif; ?>
                  </span>
                </div>
                <div class="flex justify-between">
                  <span>Kursi</span><span><?= h($it['kursi']) ?></span>
                </div>
                <div class="flex justify-between">
                  <span>Jumlah Tiket</span><span><?= (int)$it['jumlah_tiket'] ?></span>
                </div>
              </div>
            </div>

            <div class="mt-3 flex items-center justify-between">
              <div class="text-sm">
                <?php if (!empty($it['status_bayar'])): ?>
                  <span class="text-gray-500">Pembayaran: </span>
                  <span class="font-medium"><?= h($it['metode_bayar']) ?></span>
                  —
                  <span class="<?= $sudahBayarSukses ? 'text-green-600' : ($it['status_bayar']==='GAGAL' ? 'text-red-600' : 'text-yellow-600') ?>">
                    <?= h($it['status_bayar']) ?>
                  </span>
                <?php else: ?>
                  <?php if ($sudahAdaTiket): ?>
                    <span class="text-red-600">Belum dibayar</span>
                  <?php else: ?>
                    <span class="text-amber-600">Belum pilih kursi — batalkan dan ulangi pesanan</span>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
              <div class="text-base font-semibold">
                Total: Rp <?= number_format($displayTotal, 0, ',', '.') ?>
              </div>
            </div>

            <div class="mt-3 flex items-center gap-2">
              <?php if (!$sudahBayarSukses): ?>
                <?php if ($sudahAdaTiket): ?>
                  <a href="pembayaran.php?id_pemesanan=<?= h($it['id_pemesanan']) ?>"
                     class="inline-block bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm">
                    Bayar Sekarang
                  </a>
                <?php endif; ?>

                <!-- Tombol Batalkan Pesanan (hanya jika belum SUKSES) -->
                <form action="pesanan_saya.php" method="post" onsubmit="return confirm('Yakin batalkan pesanan ini?');">
                  <input type="hidden" name="id_pemesanan" value="<?= h($it['id_pemesanan']) ?>">
                  <button name="cancel_order" type="submit"
                          class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-sm">
                    Batalkan Pesanan
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <a href="index.php" class="inline-block mt-6 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl">
      Kembali ke Beranda
    </a>
  </main>
</body>
</html>
