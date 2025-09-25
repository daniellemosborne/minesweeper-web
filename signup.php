<?php
session_start();
require __DIR__ . '/db.php';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firstName = trim($_POST['fname'] ?? '');
  $lastName  = trim($_POST['lname'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $password  = $_POST['password'] ?? '';
  $confirm   = $_POST['cfrmpassword'] ?? '';

  if (!$firstName || !$lastName || !$email || !$password || !$confirm) {
    echo "<script>alert('Please fill out all fields.'); window.location.href='signup.html';</script>";
    exit;
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Invalid email format.'); window.location.href='signup.html';</script>";
    exit;
  }
  if ($password !== $confirm) {
    echo "<script>alert('Passwords do not match.'); window.location.href='signup.html';</script>";
    exit;
  }

  // email uniqueness check
  $st = $pdo->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
  $st->execute([$email]);
  if ($st->fetch()) {
    echo "<script>alert('This email is already registered.'); window.location.href='signup.html';</script>";
    exit;
  }

  // create user 
  if (!create_user($pdo, $firstName, $lastName, $email, $password)) {
    echo "<script>alert('Could not create account. Try a different email.'); window.location.href='signup.html';</script>";
    exit;
  }

  // fetch new user id
  $st = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
  $st->execute([$email]);
  $u = $st->fetch();
  $userId = $u ? (int)$u['id'] : 0;

  if ($userId > 0) {
    $pdo->prepare("INSERT INTO game_stats(user_id, games_played, games_won, time_played)
                   VALUES (?, 0, 0, 0)")->execute([$userId]);
  }

  echo "<script>alert('Account created successfully!'); window.location.href='login.html';</script>";
  exit;
}
