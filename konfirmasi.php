<?php
require "auth.php";
require "service/database.php";
require_login();

function h($x){ return htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: index.php"); exit; }

$idPemesanan = $_POST['id_pemesanan'] ?? '';
$idJadwal    = $_POST['id_jadwal'] ?? '';
$kursiDipilih= $_POST['nomor_kursi'] ?? [];

if ($idPemesanan==='' || $idJadwal==='' || empty($kursiDipilih)) {
  header("Location: index.php");
  exit;
}

// ambil pemesanan milik user
$stmt = $db->prepare("SELECT p.*, j.id_film, j.tanggal, j.jam_mulai, j.jam_selesai, f.judul, s.nama_studio, f.id_studio
  FROM pemesanan p
  JOIN jadwal_tayang j ON j.id_jadwal = ?
  JOIN film f ON f.id_film = j.id_film
  JOIN studio s ON s.id_studio = f.id_studio
  WHERE p.id_pemesanan=? AND p.id_penonton=? LIMIT 1");
$stmt->bind_param("ssi", $idJadwal, $idPemesanan, $_SESSION['user']['id']);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) { header("Location: index.php"); exit; }

$kuota = (int)$data['jumlah_tiket'];
if (count($kursiDipilih) !== $kuota) {
  // harus sesuai jumlah
  header("Location: pilih_kursi.php?id_pemesanan={$idPemesanan}&id_jadwal={$idJadwal}&id_studio={$data['id_studio']}");
  exit;
}

// harga (sederhana)
$hargaSatuan = 50000;

// Pastikan kursi belum dipakai user lain (race-condition sederhana)
$in = implode(',', array_fill(0, count($kursiDipilih), '?'));
$types = str_repeat('s', count($kursiDipilih));

$sqlCek = "SELECT nomor_kursi FROM tiket WHERE id_jadwal=? AND nomor_kursi IN ($in)";
$cek = $db->prepare($sqlCek);
$bindParams = [$idJadwal];
foreach($kursiDipilih as $k){ $bindParams[] = $k; }

// bind dinamis
$ref = [];
$ref[] = & $sqlType;
$sqlType = 's' . $types;
foreach ($bindParams as $i => $v) { $ref[] = & $bindParams[$i]; }
call_user_func_array([$cek, 'bind_param'], $ref);

$cek->execute();
$used = $cek->get_result()->fetch_all(MYSQLI_ASSOC);
$cek->close();

if (!empty($used)) {
  // ada kursi yang keburu diambil
  header("Location: pilih_kursi.php?id_pemesanan={$idPemesanan}&id_jadwal={$idJadwal}&id_studio={$data['id_studio']}");
  exit;
}

// simpan tiket
$ins = $db->prepare("INSERT INTO tiket (id_tiket, id_pemesanan, id_jadwal, nomor_kursi, harga) VALUES (?,?,?,?,?)");
foreach($kursiDipilih as $k){
  $idTiket = 'TK'.substr(time().mt_rand(100,999), -8);
  $ins->bind_param("ssssi", $idTiket, $idPemesanan, $idJadwal, $k, $hargaSatuan);
  $ins->execute();
}
$ins->close();

// ambil kursi final untuk display
$seats = [];
$in = implode(',', array_fill(0, count($kursiDipilih), '?'));
$types = str_repeat('s', count($kursiDipilih));
$q = $db->prepare("SELECT nomor_kursi FROM kursi WHERE nomor_kursi IN ($in) ORDER BY nomor_kursi");
$ref = [];
$ref[] = & $types;
foreach ($kursiDipilih as $i => $v) { $ref[] = & $kursiDipilih[$i]; }
call_user_func_array([$q, 'bind_param'], $ref);
$q->execute();
$r = $q->get_result();
while($row=$r->fetch_assoc()){ $seats[] = $row['nomor_kursi']; }
$q->close();

$total = $kuota * $hargaSatuan;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Konfirmasi — Bioskop</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <?php include "layout/navbar.php"; ?>

  <main class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Konfirmasi Pemesanan</h1>

    <div class="rounded-2xl border bg-white p-6 space-y-3">
      <div class="flex justify-between"><span>Film</span><span class="font-medium"><?= h($data['judul']) ?></span></div>
      <div class="flex justify-between"><span>Studio</span><span><?= h($data['nama_studio']) ?></span></div>
      <div class="flex justify-between"><span>Tanggal</span><span><?= h($data['tanggal']) ?></span></div>
      <div class="flex justify-between"><span>Jam</span><span><?= h(substr((string)$data['jam_mulai'],0,5)) ?> - <?= h(substr((string)$data['jam_selesai'],0,5)) ?></span></div>
      <div class="flex justify-between"><span>Kursi</span><span><?= h(implode(', ', $seats)) ?></span></div>
      <div class="flex justify-between"><span>Jumlah</span><span><?= (int)$kuota ?> tiket</span></div>
      <div class="flex justify-between"><span>Harga Satuan</span><span>Rp <?= number_format($hargaSatuan,0,',','.') ?></span></div>
      <div class="flex justify-between text-lg font-semibold"><span>Total</span><span>Rp <?= number_format($total,0,',','.') ?></span></div>
    </div>

    <p class="text-sm text-gray-500 mt-3">Tiket tersimpan. Kamu bisa mengecek riwayat pemesanan di halaman profil (fitur opsional).</p>

    <a href="index.php" class="inline-block mt-4 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl">Selesai</a>
  </main>
</body>
</html>
