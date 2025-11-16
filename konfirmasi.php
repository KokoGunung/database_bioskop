<?php
require "auth.php";
require "service/database.php";
require_login();

function h($x){ return htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8'); }

// Generator ID tiket yang aman (cek ke DB agar tidak tabrakan)
function genTicketId(mysqli $db): string {
  // 14 chars: "TK" + 12 hex (6 bytes)
  while (true) {
    $id = 'TK' . strtoupper(bin2hex(random_bytes(6)));
    $chk = $db->prepare("SELECT 1 FROM tiket WHERE id_tiket=? LIMIT 1");
    $chk->bind_param("s", $id);
    $chk->execute();
    $exists = $chk->get_result()->num_rows > 0;
    $chk->close();
    if (!$exists) return $id;
  }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: index.php"); exit; }

$idPemesanan  = $_POST['id_pemesanan'] ?? '';
$idJadwal     = $_POST['id_jadwal'] ?? '';
$kursiDipilih = $_POST['nomor_kursi'] ?? [];

if ($idPemesanan==='' || $idJadwal==='' || empty($kursiDipilih)) {
  header("Location: index.php");
  exit;
}

// Ambil data pemesanan milik user + detail jadwal/film (TERMASUK harga)
$stmt = $db->prepare("
  SELECT p.id_pemesanan, p.jumlah_tiket, p.id_penonton,
         j.id_jadwal, j.tanggal, j.jam_mulai, j.jam_selesai,
         f.judul, f.harga, s.nama_studio, f.id_studio
  FROM pemesanan p
  JOIN jadwal_tayang j ON j.id_jadwal = ?
  JOIN film f ON f.id_film = j.id_film
  JOIN studio s ON s.id_studio = f.id_studio
  WHERE p.id_pemesanan = ? AND p.id_penonton = ?
  LIMIT 1
");
$stmt->bind_param("ssi", $idJadwal, $idPemesanan, $_SESSION['user']['id']);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) { header("Location: index.php"); exit; }

$kuota       = (int)$data['jumlah_tiket'];
$hargaSatuan = (int)($data['harga'] ?? 0);

// Fallback bila harga film 0/null
if ($hargaSatuan <= 0) {
  $q = $db->prepare("
    SELECT f.harga
    FROM jadwal_tayang j
    JOIN film f ON f.id_film = j.id_film
    WHERE j.id_jadwal = ?
    LIMIT 1
  ");
  $q->bind_param("s", $idJadwal);
  $q->execute();
  $extra = $q->get_result()->fetch_assoc();
  $q->close();
  $hargaSatuan = (int)($extra['harga'] ?? 0);
}

// Validasi jumlah kursi = kuota
if (count($kursiDipilih) !== $kuota) {
  header("Location: pilih_kursi.php?id_pemesanan={$idPemesanan}&id_jadwal={$idJadwal}&id_studio={$data['id_studio']}");
  exit;
}

// Pastikan kursi belum dipakai di jadwal ini
$placeholders = implode(',', array_fill(0, count($kursiDipilih), '?'));
$typesIn = str_repeat('s', count($kursiDipilih));
$sqlCek = "SELECT nomor_kursi FROM tiket WHERE id_jadwal=? AND nomor_kursi IN ($placeholders)";
$cek = $db->prepare($sqlCek);
$typesFull = 's' . $typesIn;
$params = [$typesFull, $idJadwal];
foreach ($kursiDipilih as $k) { $params[] = $k; }
$refs = [];
foreach ($params as $i => $v) { $refs[$i] = &$params[$i]; }
call_user_func_array([$cek, 'bind_param'], $refs);
$cek->execute();
$used = $cek->get_result()->fetch_all(MYSQLI_ASSOC);
$cek->close();

if (!empty($used)) {
  header("Location: pilih_kursi.php?id_pemesanan={$idPemesanan}&id_jadwal={$idJadwal}&id_studio={$data['id_studio']}");
  exit;
}

// Simpan tiket (ID unik + harga dari film)
$ins = $db->prepare("
  INSERT INTO tiket (id_tiket, nomor_kursi, id_pemesanan, id_jadwal, harga)
  VALUES (?,?,?,?,?)
");
foreach ($kursiDipilih as $nomor) {
  $idTiket = genTicketId($db);
  $ins->bind_param("ssssi", $idTiket, $nomor, $idPemesanan, $idJadwal, $hargaSatuan);
  $ins->execute();
}
$ins->close();

$seats = $kursiDipilih;
$total = $hargaSatuan * $kuota;
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

    <a href="pesanan_saya.php" class="inline-block mt-4 mr-2 bg-white border text-gray-700 px-4 py-2 rounded-xl">Pesanan Saya</a>
    <a href="pembayaran.php?id_pemesanan=<?= h($idPemesanan) ?>" class="inline-block mt-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl">Bayar Sekarang</a>
    <a href="index.php" class="inline-block mt-4 ml-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl">Selesai</a>
  </main>
</body>
</html>
