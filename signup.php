<?php
session_start();
require __DIR__ . '/db.php';

$messages = [
  'missing_fields'    => 'Please fill out all fields.',
  'bad_email'         => 'Please enter a valid email.',
  'password_mismatch' => 'Passwords do not match.',
  'email_taken'       => 'That email is already in use.',
  'create_failed'     => 'Could not create account. Please try again.',
  'login_required'    => 'Account created, but please log in.'
];

$error = $_GET['error'] ?? '';
$errorMsg = $messages[$error] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firstName = trim($_POST['fname'] ?? '');
  $lastName  = trim($_POST['lname'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $password  = $_POST['password'] ?? '';
  $confirm   = $_POST['cfrmpassword'] ?? '';

  if (!$firstName || !$lastName || !$email || !$password || !$confirm) {
    header("Location: signup.php?error=missing_fields");
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: signup.php?error=bad_email");
    exit;
  }

  if ($password !== $confirm) {
    header("Location: signup.php?error=password_mismatch");
    exit;
  }

  $st = $pdo->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
  $st->execute([$email]);
  if ($st->fetch()) {
    header("Location: signup.php?error=email_taken");
    exit;
  }

  if (!create_user($pdo, $firstName, $lastName, $email, $password)) {
    header("Location: signup.php?error=create_failed");
    exit;
  }

  $userId = (int)$pdo->lastInsertId();
  if ($userId > 0) {
    $pdo->prepare("INSERT IGNORE INTO game_stats(user_id, games_played, games_won, time_played)
                   VALUES (?, 0, 0, 0)")->execute([$userId]);

    $_SESSION['user_id'] = $userId;
    $_SESSION['user'] = $email;

    session_write_close();
    header("Location: index.php");
    exit;
  }

  header("Location: login.php?error=login_required");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="account.css">
  <title>Create Account</title>
</head>
<body>
  <div class="signup_form">
    <h1 class="signup_heading">Create Account</h1>

    <?php if ($errorMsg): ?>
      <p style="color: red; margin-top: 10px;">
        <?= htmlspecialchars($errorMsg) ?>
      </p>
    <?php endif; ?>

    <form class="input_fields" action="signup.php" method="post" name="signupForm">
      <input type="text" id="fname" name="fname" placeholder="First name" required><br>
      <input type="text" id="lname" name="lname" placeholder="Last name" required><br>
      <input type="email" id="email" name="email" placeholder="Email address" required><br>
      <input type="password" id="create-password" name="password" placeholder="Create password" required><br>
      <input type="password" id="cfrmpassword" name="cfrmpassword" placeholder="Confirm password" required><br>

      <p>
        Have an account?
        <button type="button" class="createaccount" onclick="window.location.href='login.php';">Login</button>
      </p>

      <button type="submit" class="createaccount_btn">Sign up</button>
    </form>
  </div>
</body>
</html>
