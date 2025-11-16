<?php
require __DIR__.'/auth.php';
require __DIR__.'/service/database.php';
function h($x){return htmlspecialchars($x,ENT_QUOTES,'UTF-8');}

$idFilm = $_GET['id'] ?? '';
$film = null;

if ($idFilm) {
  $stmt = $db->prepare("SELECT f.*, s.nama_studio, s.kapasitas
                        FROM film f
                        LEFT JOIN studio s ON s.id_studio = f.id_studio
                        WHERE f.id_film = ? LIMIT 1");
  $stmt->bind_param("s", $idFilm);
  $stmt->execute();
  $film = $stmt->get_result()->fetch_assoc();
  $hargaSatuan = (int)($film['harga'] ?? 0);
  $qty         = max(1, (int)($_POST['jumlah_tiket'] ?? 1));
  $total       = $hargaSatuan * $qty;
  $stmt->close();
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['buat_pesan'])) {
  $idFilm = $_POST['id_film'] ?? '';
  $idJad  = $_POST['id_jadwal'] ?? '';
  $qty    = max(1, (int)($_POST['jumlah_tiket'] ?? 1));

  $userId = (int)($_SESSION['user']['id'] ?? 0);

  if (!$userId) {
    $error = "Silakan login terlebih dahulu.";
  } elseif (!$idFilm || !$idJad) {
    $error = "Pilih jadwal terlebih dahulu.";
  } else {
    // RE-LOAD FILM SAAT POST → agar harga tidak 0
    $stmtFilm = $db->prepare("SELECT id_film, id_studio, judul, harga FROM film WHERE id_film = ? LIMIT 1");
    $stmtFilm->bind_param("s", $idFilm);
    $stmtFilm->execute();
    $filmRow = $stmtFilm->get_result()->fetch_assoc();
    $stmtFilm->close();

    if (!$filmRow) {
      $error = "Film tidak ditemukan.";
    } else {
      $hargaSatuan = (int)($filmRow['harga'] ?? 0);
      $total       = $hargaSatuan * $qty;

      // buat pemesanan
      $idPsn = 'PM'.substr(time().mt_rand(100,999), -8);
      $stmt = $db->prepare("INSERT INTO pemesanan (id_pemesanan, id_penonton, tanggal_pesan, jumlah_tiket)
                            VALUES (?, ?, NOW(), ?)");
      $stmt->bind_param("sii", $idPsn, $userId, $qty);

      if ($stmt->execute()) {
        $stmt->close();

        // redirect ke pilih kursi (jika skrip kamu masih pakai id_studio, kirimkan; kalau tidak perlu, hapus saja)
        $idStudio = $filmRow['id_studio'];
        header("Location: pilih_kursi.php?id_pemesanan={$idPsn}&id_jadwal={$idJad}&id_studio={$idStudio}");
        exit;
      } else {
        $error = "Gagal menyimpan pemesanan.";
        $stmt->close();
      }
    }
  }
}


$jadwal = [];
if ($idFilm) {
  $stmt = $db->prepare("SELECT j.* FROM jadwal_tayang j
                        WHERE j.id_film = ? AND j.tanggal >= CURDATE()
                        ORDER BY j.tanggal ASC, j.jam_mulai ASC");
  $stmt->bind_param("s", $idFilm);
  $stmt->execute();
  $jadwal = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Pemesanan — Bioskop</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <?php include "layout/navbar.php"; ?>

  <main class="max-w-6xl mx-auto px-4 py-6">
    <?php if (!$film): ?>
      <div class="rounded-xl border bg-white p-4">Film tidak ditemukan. <a class="text-indigo-600" href="index.php">Kembali</a></div>
    <?php else: ?>
      <?php if ($success): ?>
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="alert">
          <?= h($success) ?>
        </div>
      <?php elseif ($error): ?>
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
          <?= h($error) ?>
        </div>
      <?php endif; ?>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
          <div class="rounded-2xl border bg-white p-4">
            <div class="flex items-start gap-4">
              <div class="w-28 h-36 rounded-lg bg-gray-200 grid place-items-center text-3xl">🎬</div>
              <div class="flex-1">
                <h1 class="text-2xl font-bold"><?= h($film['judul']) ?></h1>
                <div class="text-sm text-gray-600 mt-1">
                  <?= h($film['genre']) ?> • Durasi <?= h(substr((string)$film['durasi'],0,5)) ?> • <?= (int)$film['rating'] ?>+
                </div>
                <div class="text-sm text-gray-600 mt-1">
                  Studio: <?= h($film['nama_studio'] ?? $film['id_studio']) ?><?php if(!empty($film['kapasitas'])): ?> (kapasitas <?= (int)$film['kapasitas'] ?>)<?php endif; ?>
                </div>
                <p class="text-xs text-gray-500 mt-2">Tayang: <?= h($film['mulai_tayang']) ?> s.d. <?= h($film['selesai_tayang']) ?></p>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border bg-white p-4">
            <h2 class="font-semibold mb-3">Pilih Jadwal</h2>
            <?php if (empty($jadwal)): ?>
              <div class="text-sm text-gray-600">Belum ada jadwal dari hari ini.</div>
            <?php else: ?>
              <form method="post" class="space-y-4">
                <input type="hidden" name="id_film" value="<?= h($film['id_film']) ?>">
                <div class="flex flex-wrap gap-2">
                  <?php foreach($jadwal as $j): ?>
                    <label class="inline-flex items-center gap-2">
                      <input type="radio" name="id_jadwal" class="accent-indigo-600" value="<?= h($j['id_jadwal']) ?>" required>
                      <span class="px-3 py-2 rounded-lg border text-sm bg-white hover:bg-gray-50">
                        <?= h($j['tanggal']) ?> • <?= h(substr((string)$j['jam_mulai'],0,5)) ?>
                      </span>
                    </label>
                  <?php endforeach; ?>
                </div>

                <div class="flex items-center gap-3">
                  <label class="text-sm">Jumlah Tiket</label>
                  <input type="number" name="jumlah_tiket" min="1" value="1" class="w-20 border rounded-lg px-2 py-1 text-center" />
                </div>

                <button name="buat_pesan" class="mt-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-xl">
                  Buat Pemesanan
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>

        <aside class="lg:col-span-1">
          <div class="rounded-2xl border bg-white p-4 sticky top-24">
            <h3 class="font-semibold mb-3">Ringkasan</h3>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between"><span>Film</span><span class="font-medium"><?= h($film['judul']) ?></span></div>
              <div class="flex justify-between"><span>Studio</span><span><?= h($film['nama_studio'] ?? $film['id_studio']) ?></span></div>
              <div class="flex justify-between"><span>Harga Satuan</span><span>Rp <?= number_format($hargaSatuan,0,',','.') ?></span></div>
              <div class="flex justify-between text-base font-semibold"><span>Total</span><span>Rp <?= number_format($total,0,',','.') ?></span></div>

              <div class="text-xs text-gray-500">Pilih jadwal dan jumlah tiket, lalu klik “Buat Pemesanan”.</div>
            </div>
          </div>
        </aside>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
