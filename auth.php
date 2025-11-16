<?php
// auth.php
session_start();
if (empty($_SESSION['user']['id'])) {
  $next = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
  header("Location: login.php?next={$next}");
  exit;
}
