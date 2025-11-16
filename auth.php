<?php
session_start();

function require_login(): void {
  if (empty($_SESSION['user'])) {
    $next = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
    header("Location: login.php?next={$next}");
    exit;
  }
}

function require_admin(): void {
  if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit;
  }
}
