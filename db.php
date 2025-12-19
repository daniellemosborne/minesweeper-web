<?php
$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

$dsn = "mysql:unix_socket=$host;dbname=$db;charset=utf8mb4";

  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

// users table
$pdo->exec("
  CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name  VARCHAR(255) NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// game stats (per user)
$pdo->exec("
  CREATE TABLE IF NOT EXISTS game_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    games_played INT NOT NULL DEFAULT 0,
    games_won    INT NOT NULL DEFAULT 0,
    time_played  INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_game_stats_user
      FOREIGN KEY (user_id) REFERENCES users(id)
      ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

try {
  $pdo->exec("CREATE INDEX idx_gamestats_user ON game_stats(user_id);");
} catch (Throwable $e) {
  // ignore if it already exists
}

function create_user(PDO $pdo, string $first, string $last, string $email, string $password): bool
{
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $st = $pdo->prepare("INSERT INTO users(first_name, last_name, email, password) VALUES (?,?,?,?)");
  try {
    return $st->execute([$first, $last, $email, $hash]);
  } catch (Throwable $e) {
    return false;
  }
}

function find_user_by_email(PDO $pdo, string $email): ?array
{
  $st = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $st->execute([$email]);
  $u = $st->fetch();
  return $u ?: null;
}

function verify_login(PDO $pdo, string $email, string $password): ?array
{
  $u = find_user_by_email($pdo, $email);
  if ($u && password_verify($password, $u['password'])) return $u;
  return null;
}

function get_or_create_stats(PDO $pdo, int $userId): array
{
  $st = $pdo->prepare("SELECT * FROM game_stats WHERE user_id = ?");
  $st->execute([$userId]);
  $row = $st->fetch();
  if ($row) return $row;

  $pdo->prepare("INSERT INTO game_stats(user_id) VALUES (?)")->execute([$userId]);
  $st->execute([$userId]);
  return $st->fetch();
}

function update_stats(PDO $pdo, int $userId, int $playedDelta, int $wonDelta, int $timeDeltaMs): void
{
  get_or_create_stats($pdo, $userId); // ensure row exists
  $pdo->prepare("
    UPDATE game_stats
       SET games_played = games_played + ?,
           games_won    = games_won    + ?,
           time_played  = time_played  + ?
     WHERE user_id = ?
  ")->execute([$playedDelta, $wonDelta, $timeDeltaMs, $userId]);
}
