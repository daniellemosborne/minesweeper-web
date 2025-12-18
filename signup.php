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
    header("Location: signup.html?error=missing_fields");
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: signup.html?error=bad_email");
    exit;
  }

  if ($password !== $confirm) {
    header("Location: signup.html?error=password_mismatch");
    exit;
  }

  // email uniqueness check
  $st = $pdo->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
  $st->execute([$email]);
  if ($st->fetch()) {
    header("Location: signup.html?error=email_taken");
    exit;
  }

  // create user 
  if (!create_user($pdo, $firstName, $lastName, $email, $password)) {
    header("Location: signup.html?error=create_failed");
    exit;
  }

  // get user id
  $userId = (int)$pdo->lastInsertId();

  if ($userId > 0) {
    $pdo->prepare("INSERT INTO game_stats(user_id, games_played, games_won, time_played)
                   VALUES (?, 0, 0, 0)")->execute([$userId]);

    // send them to main menu
    $_SESSION['user_id'] = $userId;
    $_SESSION['user'] = $email; 

    session_write_close();
    header("Location: index.php");
    exit;
  }
  
  header("Location: login.html?error=login_required");
  exit;
}

header("Location: signup.html");
exit;


