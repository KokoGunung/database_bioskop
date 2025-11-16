<?php
require "auth.php";
require "service/database.php";
require_login();

function h($x){ return htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8'); }

// param wajib
$idPemesanan = $_GET['id_pemesanan'] ?? '';
$idJadwal    = $_GET['id_jadwal'] ?? '';
$idStudio    = $_GET['id_studio'] ?? '';

if ($idPemesanan==='' || $idJadwal==='' || $idStudio==='') {
  header("Location: index.php");
  exit;
}

// ambil pemesanan (cek milik user)
$stmt = $db->prepare("SELECT p.*, pe.email, pe.nama FROM pemesanan p 
  JOIN penonton pe ON pe.id_penonton = p.id_penonton
  WHERE p.id_pemesanan=? AND p.id_penonton=? LIMIT 1");
$stmt->bind_param("si", $idPemesanan, $_SESSION['user']['id']);
$stmt->execute();
$pemesanan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pemesanan) {
  header("Location: index.php");
  exit;
}

// ambil kursi di studio
$stmt = $db->prepare("SELECT k.nomor_kursi, k.nomor_kursi 
  FROM kursi k WHERE k.id_studio=? ORDER BY k.nomor_kursi");
$stmt->bind_param("s", $idStudio);
$stmt->execute();
$kursi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// kursi yang sudah dipakai pada jadwal ini
$terpakai = [];
$q = $db->prepare("SELECT t.nomor_kursi FROM tiket t WHERE t.id_jadwal=?");
$q->bind_param("s", $idJadwal);
$q->execute();
$res = $q->get_result();
while($r=$res->fetch_assoc()){ $terpakai[$r['nomor_kursi']] = true; }
$q->close();

$kuota = (int)$pemesanan['jumlah_tiket'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Pilih Kursi — Bioskop</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function limitSelection(max) {
      const checks = document.querySelectorAll('input[name="nomor_kursi[]"]');
      let count = 0;
      checks.forEach(ch => { if (ch.checked) count++; });
      if (count > max) {
        alert("Kamu hanya boleh memilih " + max + " kursi.");
        // uncheck yang terakhir
        const last = Array.from(checks).reverse().find(ch => ch.checked);
        if (last) last.checked = false;
      }
    }
  </script>
</head>
<body class="bg-gray-50 text-gray-900">
  <?php include "layout/navbar.php"; ?>

  <main class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Pilih Kursi</h1>
    <p class="text-gray-600 mb-4">Silakan pilih <?= $kuota ?> kursi.</p>

    <form action="konfirmasi.php" method="post" class="space-y-4">
      <input type="hidden" name="id_pemesanan" value="<?= h($idPemesanan) ?>">
      <input type="hidden" name="id_jadwal" value="<?= h($idJadwal) ?>">

      <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
        <?php foreach($kursi as $k): 
          $disabled = isset($terpakai[$k['nomor_kursi']]);
        ?>
          <label class="relative">
            <input type="checkbox" name="nomor_kursi[]" value="<?= h($k['nomor_kursi']) ?>" 
                   class="peer sr-only"
                   <?php if($disabled) echo 'disabled'; ?>
                   onclick="limitSelection(<?= $kuota ?>)">
            <div class="px-3 py-2 border rounded-xl text-center text-sm 
                        <?php if($disabled): ?>
                          bg-gray-200 text-gray-400 cursor-not-allowed
                        <?php else: ?>
                          bg-white hover:bg-gray-50 peer-checked:bg-indigo-600 peer-checked:text-white
                        <?php endif; ?>">
              <?= h($k['nomor_kursi']) ?>
            </div>
          </label>
        <?php endforeach; ?>
      </div>

      <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-xl">
        Lanjutkan
      </button>
    </form>
  </main>
</body>
</html>
