<?php
require "auth.php";
require "service/database.php";
require_admin();

function h($x){ return htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8'); }

$ok = ''; $err = '';
$studios = $db->query("SELECT id_studio, nama_studio FROM studio ORDER BY nama_studio")->fetch_all(MYSQLI_ASSOC);
$films   = $db->query("SELECT id_film, judul FROM film ORDER BY judul")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD']==='POST') {

  if (isset($_POST['add_studio'])) {
    $id  = trim($_POST['id_studio'] ?? '');
    $nm  = trim($_POST['nama_studio'] ?? '');
    $kap = (int)($_POST['kapasitas'] ?? 0);
    if ($id==='' || $nm==='' || $kap<=0) { $err='Lengkapi id, nama, kapasitas.'; }
    else {
      $st = $db->prepare("INSERT INTO studio (id_studio, nama_studio, kapasitas) VALUES (?,?,?)");
      $st->bind_param("ssi", $id, $nm, $kap);
      $ok = $st->execute() ? 'Studio ditambahkan.' : 'Gagal menambah studio.';
      $st->close();
    }
  }

  if (isset($_POST['add_film'])) {
    $idf = trim($_POST['id_film'] ?? '');
    $ids = trim($_POST['id_studio_ref'] ?? '');
    $jd  = trim($_POST['judul'] ?? '');
    $ge  = trim($_POST['genre'] ?? '');
    $du  = trim($_POST['durasi'] ?? '');   // HH:MM:SS
    $rt  = (int)($_POST['rating'] ?? 13);
    $si  = trim($_POST['sinopsis'] ?? '');
    $mt  = trim($_POST['mulai_tayang'] ?? '');   // YYYY-MM-DD
    $stg = trim($_POST['selesai_tayang'] ?? ''); // YYYY-MM-DD
    if ($idf===''||$ids===''||$jd===''||$du===''||$mt===''||$stg==='') { $err='Lengkapi field wajib film.'; }
    else {
      $st = $db->prepare("INSERT INTO film (id_film,id_studio,judul,genre,durasi,rating,sinopsis,mulai_tayang,selesai_tayang)
                          VALUES (?,?,?,?,?,?,?,?,?)");
      $st->bind_param("sssssiiss", $idf,$ids,$jd,$ge,$du,$rt,$si,$mt,$stg);
      $ok = $st->execute() ? 'Film ditambahkan.' : 'Gagal menambah film.';
      $st->close();
    }
  }

  if (isset($_POST['add_jadwal'])) {
    $idj = trim($_POST['id_jadwal'] ?? '');
    $idf = trim($_POST['id_film_ref'] ?? '');
    $tg  = trim($_POST['tanggal'] ?? '');
    $jm  = trim($_POST['jam_mulai'] ?? '');
    $js  = trim($_POST['jam_selesai'] ?? '');
    if ($idj===''||$idf===''||$tg===''||$jm===''||$js==='') { $err='Lengkapi semua field jadwal.'; }
    else {
      $st = $db->prepare("INSERT INTO jadwal_tayang (id_jadwal,id_film,tanggal,jam_mulai,jam_selesai) VALUES (?,?,?,?,?)");
      $st->bind_param("sssss", $idj,$idf,$tg,$jm,$js);
      $ok = $st->execute() ? 'Jadwal ditambahkan.' : 'Gagal menambah jadwal.';
      $st->close();
    }
  }

  if (isset($_POST['add_kursi'])) {
    $idk = trim($_POST['id_kursi'] ?? '');
    $ids = trim($_POST['id_studio_for_seat'] ?? '');
    $no  = trim($_POST['nomor_kursi'] ?? '');
    if ($idk===''||$ids===''||$no==='') { $err='Lengkapi id kursi, studio, nomor kursi.'; }
    else {
      $st = $db->prepare("INSERT INTO kursi (id_kursi, id_studio, nomor_kursi) VALUES (?,?,?)");
      $st->bind_param("sss", $idk,$ids,$no);
      $ok = $st->execute() ? 'Kursi ditambahkan.' : 'Gagal menambah kursi.';
      $st->close();
    }
  }

  // refresh dropdown
  $studios = $db->query("SELECT id_studio, nama_studio FROM studio ORDER BY nama_studio")->fetch_all(MYSQLI_ASSOC);
  $films   = $db->query("SELECT id_film, judul FROM film ORDER BY judul")->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin — Bioskop</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <?php include "layout/navbar.php"; ?>

  <main class="max-w-6xl mx-auto px-4 py-8">
    <header class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl md:text-3xl font-bold">Admin Panel</h1>
    </header>

    <?php if ($ok): ?>
      <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"><?= h($ok) ?></div>
    <?php endif; ?>
    <?php if ($err): ?>
      <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= h($err) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <section class="rounded-2xl border bg-white p-6">
        <h2 class="font-semibold text-lg mb-4">Tambah Studio</h2>
        <form method="post" class="space-y-3">
          <input type="hidden" name="add_studio" value="1">
          <div><label class="text-sm">ID Studio</label><input name="id_studio" class="w-full border rounded-xl px-3 py-2" placeholder="ST01" required></div>
          <div><label class="text-sm">Nama Studio</label><input name="nama_studio" class="w-full border rounded-xl px-3 py-2" required></div>
          <div><label class="text-sm">Kapasitas</label><input name="kapasitas" type="number" min="1" class="w-full border rounded-xl px-3 py-2" required></div>
          <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl">Simpan</button>
        </form>
      </section>

      <section class="rounded-2xl border bg-white p-6">
        <h2 class="font-semibold text-lg mb-4">Tambah Film</h2>
        <form method="post" class="space-y-3">
          <input type="hidden" name="add_film" value="1">
          <div><label class="text-sm">ID Film</label><input name="id_film" class="w-full border rounded-xl px-3 py-2" placeholder="F001" required></div>
          <div>
            <label class="text-sm">Studio</label>
            <select name="id_studio_ref" class="w-full border rounded-xl px-3 py-2" required>
              <option value="">-- pilih studio --</option>
              <?php foreach($studios as $s): ?>
                <option value="<?= h($s['id_studio']) ?>"><?= h($s['id_studio'].' — '.$s['nama_studio']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div><label class="text-sm">Judul</label><input name="judul" class="w-full border rounded-xl px-3 py-2" required></div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="text-sm">Genre</label><input name="genre" class="w-full border rounded-xl px-3 py-2"></div>
            <div><label class="text-sm">Durasi (HH:MM:SS)</label><input name="durasi" class="w-full border rounded-xl px-3 py-2" placeholder="01:45:00" required></div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="text-sm">Rating Umur</label><input name="rating" type="number" min="0" class="w-full border rounded-xl px-3 py-2" value="13"></div>
            <div><label class="text-sm">Mulai Tayang</label><input name="mulai_tayang" type="date" class="w-full border rounded-xl px-3 py-2" required></div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="text-sm">Selesai Tayang</label><input name="selesai_tayang" type="date" class="w-full border rounded-xl px-3 py-2" required></div>
          </div>
          <div><label class="text-sm">Sinopsis</label><textarea name="sinopsis" rows="3" class="w-full border rounded-xl px-3 py-2"></textarea></div>
          <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl">Simpan</button>
        </form>
      </section>

      <section class="rounded-2xl border bg-white p-6">
        <h2 class="font-semibold text-lg mb-4">Tambah Jadwal Tayang</h2>
        <form method="post" class="space-y-3">
          <input type="hidden" name="add_jadwal" value="1">
          <div><label class="text-sm">ID Jadwal</label><input name="id_jadwal" class="w-full border rounded-xl px-3 py-2" placeholder="J101" required></div>
          <div>
            <label class="text-sm">Film</label>
            <select name="id_film_ref" class="w-full border rounded-xl px-3 py-2" required>
              <option value="">-- pilih film --</option>
              <?php foreach($films as $f): ?>
                <option value="<?= h($f['id_film']) ?>"><?= h($f['id_film'].' — '.$f['judul']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div><label class="text-sm">Tanggal</label><input name="tanggal" type="date" class="w-full border rounded-xl px-3 py-2" required></div>
            <div><label class="text-sm">Jam Mulai</label><input name="jam_mulai" type="time" class="w-full border rounded-xl px-3 py-2" required></div>
            <div><label class="text-sm">Jam Selesai</label><input name="jam_selesai" type="time" class="w-full border rounded-xl px-3 py-2" required></div>
          </div>
          <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl">Simpan</button>
        </form>
      </section>

      <section class="rounded-2xl border bg-white p-6">
        <h2 class="font-semibold text-lg mb-4">Tambah Kursi</h2>
        <form method="post" class="space-y-3">
          <input type="hidden" name="add_kursi" value="1">
          <div><label class="text-sm">ID Kursi</label><input name="id_kursi" class="w-full border rounded-xl px-3 py-2" placeholder="K001" required></div>
          <div>
            <label class="text-sm">Studio</label>
            <select name="id_studio_for_seat" class="w-full border rounded-xl px-3 py-2" required>
              <option value="">-- pilih studio --</option>
              <?php foreach($studios as $s): ?>
                <option value="<?= h($s['id_studio']) ?>"><?= h($s['id_studio'].' — '.$s['nama_studio']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div><label class="text-sm">Nomor Kursi</label><input name="nomor_kursi" class="w-full border rounded-xl px-3 py-2" placeholder="A1 / 1 / B12" required></div>
          <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl">Simpan</button>
        </form>
      </section>
    </div>
  </main>
</body>
</html>
