<?php
session_start();
require __DIR__ . '/db.php';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  if (!$email || !$pass) {
    echo "<script>alert('Please enter email and password.'); window.location.href='login.html';</script>";
    exit;
  }

  // look up user by email
  $st = $pdo->prepare("SELECT id, email, password FROM users WHERE email = ? LIMIT 1");
  $st->execute([$email]);
  $u = $st->fetch();

  if ($u) {
    $stored = $u['password'];

    $ok = false;
    if (preg_match('/^\$2[ayb]\$/', $stored)) {
      $ok = password_verify($pass, $stored);
    } else {
      $ok = hash_equals((string)$stored, (string)$pass);
    }

    if ($ok) {
      $_SESSION['user_id'] = (int)$u['id'];
      $_SESSION['user']    = $u['email'];  // used by leaderboard.php
      echo "<script>alert('Login successful!'); window.location.href='index.php';</script>";
      exit;
    } else {
      echo "<script>alert('Invalid password.'); window.location.href='login.html';</script>";
      exit;
    }
  } else {
    echo "<script>alert('Account with this email does not exist.'); window.location.href='login.html';</script>";
    exit;
  }
}
