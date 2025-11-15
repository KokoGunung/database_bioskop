<?php
  include "service/database.php";
  $message = "";
  
  if(isset($_POST["confirm"])){
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM penonton 
    WHERE email='$email' AND password='$password'";

    $result = $db->query($sql);
  
    if($result->num_rows>0){
      header("location: index.php");
    } else{
      $message = "Email atau password salah!";
    }
  }


?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login — Bioskop</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>html{scroll-behavior:smooth}</style>
</head>

<body class="bg-gray-50 text-gray-900">
  <!-- NAVBAR SECTION -->
  <?php include "layout/navbar.html" ?>
  <!-- END OF NAVBAR SECTION -->

  <main class="max-w-6xl mx-auto px-4 py-10">
    <div class="mx-auto max-w-md">
      <div class="text-center mb-6">
        <h1 class="text-2xl font-bold">Login</h1>
        <p class="text-gray-600 mt-1">Masukkan email dan password.</p>
      </div>

      <div class="rounded-2xl border bg-white shadow-sm p-6">
        <form action="login.php" method="POST" class="space-y-4">
          <div class="space-y-1.5">
            <label for="email" class="text-sm font-medium">Email</label>
            <input name="email" type="email" required
              class="w-full rounded-xl border px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>

          <div class="space-y-1.5">
            <label for="password" class="text-sm font-medium">Password</label>
            <input name="password" type="password" required minlength="6"
              class="w-full rounded-xl border px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <p class="mb-3 text-sm text-red-600" role="alert"> <?= $message ?></p>

          <button name="confirm" type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2.5 rounded-xl">
            Masuk
          </button>
        </form>
      </div>

      <p class="text-sm text-gray-600 text-center mt-6">
        Belum punya akun? <a href="register.php" class="text-indigo-600 hover:text-indigo-700 font-medium">Daftar</a>
      </p>
    </div>
  </main>

  <footer class="border-t py-8 mt-8 text-center text-sm text-gray-500">
    &copy; 2025 Kelompok 1 IF-D
  </footer>
</body>
</html>
