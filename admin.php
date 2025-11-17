<?php
require "auth.php";
require "service/database.php";
require_admin();
function h($x){ return htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8'); }

// Tambah waktu HH:MM:SS (jam_mulai + durasi) -> HH:MM:SS
function calc_end_time(string $start, string $durasi): string {
  // normalisasi
  if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $start))   $start  = ($start  === '' ? '00:00:00' : $start.':00');
  if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $durasi)) $durasi = ($durasi === '' ? '00:00:00' : $durasi.':00');

  list($sh,$sm,$ss) = array_map('intval', explode(':', $start));
  list($dh,$dm,$ds) = array_map('intval', explode(':', $durasi));
  $startSec = $sh*3600 + $sm*60 + $ss;
  $durSec   = $dh*3600 + $dm*60 + $ds;

  $endSec = ($startSec + $durSec) % 86400; // jika lewat tengah malam, wrap ke 0-23:59:59
  $eh = floor($endSec/3600); $endSec %= 3600;
  $em = floor($endSec/60);   $es = $endSec%60;

  return sprintf('%02d:%02d:%02d', $eh, $em, $es);
}

$ok = "";
$err = "";

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

  // === Tambah Film ===
if (isset($_POST['add_film'])) {
  $idf = trim($_POST['id_film'] ?? '');
  $ids = trim($_POST['id_studio_ref'] ?? '');
  $jd  = trim($_POST['judul'] ?? '');
  $ge  = trim($_POST['genre'] ?? '');
  $du  = trim($_POST['durasi'] ?? '');       // HH:MM:SS
  $rt  = (int)($_POST['rating'] ?? 0);       // INT
  $hg  = (int)($_POST['harga'] ?? 0);        // INT
  $si  = trim($_POST['sinopsis'] ?? '');
  $mt  = trim($_POST['mulai_tayang'] ?? ''); // YYYY-MM-DD
  $stg = trim($_POST['selesai_tayang'] ?? '');// YYYY-MM-DD

  if ($idf===''||$ids===''||$jd===''||$du===''||$mt===''||$stg===''||$hg<=0) {
    $err = 'Lengkapi field wajib film (termasuk harga).';
  } else {
    $sql = "INSERT INTO film
            (id_film,id_studio,judul,genre,durasi,rating,harga,sinopsis,mulai_tayang,selesai_tayang)
            VALUES (?,?,?,?,?,?,?,?,?,?)";
    $st = $db->prepare($sql);

    // 5 string + 2 int + 3 string = 'sssssiisss'
    $st->bind_param('sssssiisss',
      $idf, $ids, $jd, $ge, $du,  // sssss
      $rt, $hg,                   // ii
      $si, $mt, $stg              // sss
    );

    if ($st->execute()) {
      $ok = 'Film ditambahkan.';
    } else {
      $err = 'Gagal menambah film.';
    }
    $st->close();
  }
}


  if (isset($_POST['add_jadwal'])) {
  $id_jadwal = trim($_POST['id_jadwal'] ?? '');
  $id_film   = trim($_POST['id_film_ref'] ?? $_POST['id_film'] ?? '');
  $tanggal   = trim($_POST['tanggal'] ?? '');
  $jam_mulai = trim($_POST['jam_mulai'] ?? '');

  if ($id_jadwal==='' || $id_film==='' || $tanggal==='' || $jam_mulai==='') {
    $err = 'Lengkapi ID Jadwal, Film, Tanggal, dan Jam Mulai.';
  } else {
    // ambil durasi film
    $q = $db->prepare("SELECT durasi FROM film WHERE id_film=? LIMIT 1");
    $q->bind_param("s", $id_film);
    $q->execute();
    $row = $q->get_result()->fetch_assoc();
    $q->close();

    if (!$row || empty($row['durasi'])) {
      $err = 'Durasi film tidak ditemukan.';
    } else {
      $jam_selesai = calc_end_time($jam_mulai, $row['durasi']);

      $st = $db->prepare("
        INSERT INTO jadwal_tayang (id_jadwal, id_film, tanggal, jam_mulai, jam_selesai)
        VALUES (?,?,?,?,?)
      ");
      $st->bind_param("sssss", $id_jadwal, $id_film, $tanggal, $jam_mulai, $jam_selesai);

      if ($st->execute()) {
        $ok = 'Jadwal berhasil ditambahkan.';
      } else {
        $err = 'Gagal menambah jadwal.';
      }
      $st->close();
    }
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
            <div><label class="text-sm">Harga (Rupiah)</label><input name="harga" type="number" min="0" class="w-full border rounded-xl px-3 py-2" placeholder="50000" required></div>
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
          <input type="hidden" name="add_jadwal" value="1" />

          <div>
            <label class="block text-sm font-medium">ID Jadwal</label>
            <input name="id_jadwal" class="w-full border rounded px-3 py-2" required>
          </div>

          <!-- PILIH FILM -->
          <div>
            <label class="block text-sm font-medium">Film</label>
            <select name="id_film" id="filmSelect" class="w-full border rounded px-3 py-2" required>
              <?php
              $fs = $db->query("SELECT id_film, judul, durasi FROM film ORDER BY judul");
              while($f = $fs->fetch_assoc()):
              ?>
                <option value="<?= h($f['id_film']) ?>" data-dur="<?= h($f['durasi']) ?>">
                  <?= h($f['judul']) ?> (<?= h(substr($f['durasi'],0,5)) ?>)
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- TAMPILKAN ID FILM YANG TERPILIH -->
          <div>
            <label class="block text-sm font-medium">ID Film (terpilih)</label>
            <input type="text" id="idFilmShown" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium">Tanggal</label>
              <input type="date" name="tanggal" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
              <label class="block text-sm font-medium">Jam Mulai</label>
              <input type="time" name="jam_mulai" id="jamMulai" step="60" class="w-full border rounded px-3 py-2" required>
            </div>
          </div>

          <!-- Preview jam selesai (otomatis) -->
          <div>
            <label class="block text-sm font-medium">Jam Selesai (otomatis)</label>
            <input type="text" id="jamSelesaiPreview" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
          </div>

          <button class="bg-indigo-600 text-white px-4 py-2 rounded">Simpan Jadwal</button>
        </form>

        <script>
        // Sinkronkan ID Film & preview jam selesai
        const filmSel  = document.getElementById('filmSelect');
        const idShown  = document.getElementById('idFilmShown');
        const jamMulai = document.getElementById('jamMulai');
        const out      = document.getElementById('jamSelesaiPreview');

        function toSec(hms){ const [h=0,m=0,s=0]=hms.split(':').map(Number); return h*3600+m*60+(s||0); }
        function toHMS(sec){ sec=((sec%86400)+86400)%86400; const h=String(Math.floor(sec/3600)).padStart(2,'0'); sec%=3600; const m=String(Math.floor(sec/60)).padStart(2,'0'); const s=String(sec%60).padStart(2,'0'); return `${h}:${m}:${s}`; }

        function syncId(){ idShown.value = filmSel.value || ''; }
        function recalc(){
          const dur = (filmSel.selectedOptions[0]?.dataset?.dur || '00:00:00');
          let jm = jamMulai.value || '00:00';
          if (jm.length===5) jm += ':00';
          out.value = toHMS(toSec(jm) + toSec(dur));
        }

        filmSel.addEventListener('change', ()=>{ syncId(); recalc(); });
        jamMulai.addEventListener('input', recalc);

        // init saat halaman dibuka
        syncId(); recalc();
        </script>


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
