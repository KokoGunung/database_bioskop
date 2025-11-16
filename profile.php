<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/service/database.php';

function h($x){ return htmlspecialchars($x, ENT_QUOTES, 'UTF-8'); }

$userId = (int)$_SESSION['user']['id'];
$message = '';
$success = '';

// Ambil data user saat ini
$stmt = $db->prepare("SELECT id_penonton, email, nama, no_hp, password FROM penonton WHERE id_penonton = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$current) {
  header('Location: logout.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Ambil input (boleh kosong; kosong = pakai nilai lama)
  $inEmail = trim($_POST['email'] ?? '');
  $inNama  = trim($_POST['nama'] ?? '');
  $inNoHp  = trim($_POST['no_hp'] ?? '');
  $passNew = $_POST['password'] ?? '';

  // Resolve nilai final yang akan disimpan
  $newEmail = ($inEmail === '') ? $current['email'] : $inEmail;
  $newNama  = ($inNama  === '') ? ($current['nama'] ?? '') : $inNama;
  $newNoHp  = ($inNoHp  === '') ? ($current['no_hp'] ?? '') : $inNoHp;

  // Cek: ada perubahan atau tidak?
  $changed = false;
  if ($newEmail !== $current['email']) $changed = true;
  if ($newNama  !== ($current['nama'] ?? '')) $changed = true;
  if ($newNoHp  !== ($current['no_hp'] ?? '')) $changed = true;
  if ($passNew !== '') $changed = true;

  if (!$changed) {
    $message = 'Tidak ada perubahan data.';
  } else {
    // Jika email berubah → cek duplikasi
    if ($newEmail !== $current['email']) {
      $stmt = $db->prepare("SELECT COUNT(*) AS n FROM penonton WHERE email = ? AND id_penonton <> ?");
      $stmt->bind_param("si", $newEmail, $userId);
      $stmt->execute();
      $dup = $stmt->get_result()->fetch_assoc();
      $stmt->close();
      if (!empty($dup['n'])) {
        $message = 'Email sudah digunakan pengguna lain.';
      }
    }

    if ($message === '') {
      // Susun UPDATE: password diubah hanya jika diisi
      if ($passNew !== '') {
        // === Versi A (password PLAIN mengikuti repo kamu) ===
        $stmt = $db->prepare("UPDATE penonton SET email=?, nama=?, no_hp=?, password=? WHERE id_penonton=?");
        $stmt->bind_param("ssssi", $newEmail, $newNama, $newNoHp, $passNew, $userId);

        /* === Versi B (rekomendasi HASHED)
        $hash = password_hash($passNew, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE penonton SET email=?, nama=?, no_hp=?, password=? WHERE id_penonton=?");
        $stmt->bind_param("ssssi", $newEmail, $newNama, $newNoHp, $hash, $userId);
        === */
      } else {
        $stmt = $db->prepare("UPDATE penonton SET email=?, nama=?, no_hp=? WHERE id_penonton=?");
        $stmt->bind_param("sssi", $newEmail, $newNama, $newNoHp, $userId);
      }

      if ($stmt->execute()) {
        $success = 'Profil berhasil diperbarui.';
        $stmt->close();

        // Segarkan data session (agar navbar ikut berubah)
        $_SESSION['user']['email'] = $newEmail;
        $_SESSION['user']['nama']  = $newNama ?: $newEmail;

        // Refresh $current untuk render ulang form
        $stmt = $db->prepare("SELECT id_penonton, email, nama, no_hp, password FROM penonton WHERE id_penonton = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc();
        $stmt->close();
      } else {
        $message = 'Gagal memperbarui profil.';
        $stmt->close();
      }
    }
  }
}

?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profil — Bioskop</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <?php include "layout/navbar.php"; ?>

  <main class="max-w-6xl mx-auto px-4 py-8">
    <header class="mb-6">
      <h1 class="text-2xl md:text-3xl font-bold">Profil Saya</h1>
      <p class="text-gray-600 mt-1">Ubah informasi akunmu di sini.</p>
    </header>

    <?php if ($success): ?>
      <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="alert">
        <?= h($success) ?>
      </div>
    <?php endif; ?>

    <?php if ($message): ?>
      <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <?= h($message) ?>
      </div>
    <?php endif; ?>

    <div class="rounded-2xl border bg-white shadow-sm p-6 max-w-xl">
      <form method="post" class="space-y-4" autocomplete="off">
        <div class="space-y-1.5">
          <label class="text-sm font-medium" for="email">Email</label>
          <input id="email" name="email" type="email" required
                 value="<?= h($current['email']) ?>"
                 class="w-full rounded-xl border px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-medium" for="nama">Nama Lengkap</label>
          <input id="nama" name="nama" type="text" required
                 value="<?= h($current['nama'] ?? '') ?>"
                 class="w-full rounded-xl border px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-medium" for="no_hp">Nomor HP</label>
          <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" pattern="[0-9]{8,15}" required
                 placeholder="08xxxxxxxxxx"
                 value="<?= h($current['no_hp'] ?? '') ?>"
                 class="w-full rounded-xl border px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500">
          <p class="text-xs text-gray-500">Angka saja, 8–15 digit.</p>
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-medium" for="password">Password Baru (opsional)</label>
          <input id="password" name="password" type="password" minlength="6"
                 placeholder="Kosongkan jika tidak ingin mengubah"
                 class="w-full rounded-xl border px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500">
          <p class="text-xs text-gray-500">Isi hanya jika ingin mengganti password.</p>
        </div>

        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2.5 rounded-xl">
          Simpan Perubahan
        </button>
      </form>
    </div>
  </main>
</body>
</html>
