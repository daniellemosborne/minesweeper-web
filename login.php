<?php
session_start();
require __DIR__ . '/db.php';

$messages = [
  'missing_fields' => 'Please enter email and password.',
  'no_account'     => 'Account with this email does not exist.',
  'bad_password'   => 'Invalid password.',
];

$error = $_GET['error'] ?? '';
$errorMsg = $messages[$error] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  if (!$email || !$pass) {
    header("Location: login.php?error=missing_fields");
    exit;
  }

  // look up user by email
  $st = $pdo->prepare("SELECT id, email, password FROM users WHERE email = ? LIMIT 1");
  $st->execute([$email]);
  $u = $st->fetch();

  if (!$u) {
    header("Location: login.php?error=no_account");
    exit;
  }

  // verify password 
  $ok = password_verify($pass, $u['password']);
  if (!$ok) {
    header("Location: login.php?error=bad_password");
    exit;
  }

  $_SESSION['user_id'] = (int)$u['id'];
  $_SESSION['user']    = $u['email']; // used by leaderboard.php

  session_write_close();
  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="account.css">
</head>

<body>
  <div class="login_form">
    <h1 class="login_heading">Minesweeper</h1>
    <h3 class="login_subheading">Login</h3>

    <?php if ($errorMsg): ?>
      <p style="color: red; margin-top: 10px;"><?= htmlspecialchars($errorMsg) ?></p>
    <?php endif; ?>

    <form class="input_fields" action="login.php" method="post">
      <input type="email" id="email" name="email" placeholder="Email address" required><br>
      <input type="password" id="password" name="password" placeholder="Enter password" required><br>

      <button type="submit" class="login_button">Login</button><br>

      <p>
        Don't have an account?
        <button type="button" class="createaccount" onclick="window.location.href='signup.php';">Sign up</button>
      </p>
    </form>
  </div>
</body>
</html>
