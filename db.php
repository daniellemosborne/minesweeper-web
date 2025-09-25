<?php
$dbFile   = getenv('DB_FILE') ?: '/tmp/minesweeper.sqlite';
$needInit = !file_exists($dbFile);

$pdo = new PDO('sqlite:' . $dbFile, null, null, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$pdo->exec('PRAGMA foreign_keys = ON');

if ($needInit) {
  // users table
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
      id           INTEGER PRIMARY KEY AUTOINCREMENT,
      first_name   TEXT NOT NULL,
      last_name    TEXT NOT NULL,
      email        TEXT NOT NULL UNIQUE,
      password     TEXT NOT NULL,              -- store password_hash here
      created_at   TEXT NOT NULL DEFAULT (datetime('now'))
    );
  ");

  // game stats (per user)
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS game_stats (
      id            INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id       INTEGER NOT NULL,
      games_played  INTEGER NOT NULL DEFAULT 0,
      games_won     INTEGER NOT NULL DEFAULT 0,
      time_played   INTEGER NOT NULL DEFAULT 0,
      FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    CREATE INDEX IF NOT EXISTS idx_gamestats_user ON game_stats(user_id);
  ");
}

function create_user(PDO $pdo, string $first, string $last, string $email, string $password): bool {
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $st = $pdo->prepare("INSERT INTO users(first_name, last_name, email, password) VALUES (?,?,?,?)");
  try { return $st->execute([$first, $last, $email, $hash]); }
  catch (Throwable $e) { return false; }
}

function find_user_by_email(PDO $pdo, string $email): ?array {
  $st = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $st->execute([$email]);
  $u = $st->fetch();
  return $u ?: null;
}

function verify_login(PDO $pdo, string $email, string $password): ?array {
  $u = find_user_by_email($pdo, $email);
  if ($u && password_verify($password, $u['password'])) return $u;
  return null;
}

function get_or_create_stats(PDO $pdo, int $userId): array {
  $st = $pdo->prepare("SELECT * FROM game_stats WHERE user_id = ?");
  $st->execute([$userId]);
  $row = $st->fetch();
  if ($row) return $row;

  $pdo->prepare("INSERT INTO game_stats(user_id) VALUES (?)")->execute([$userId]);
  $st->execute([$userId]);
  return $st->fetch();
}

function update_stats(PDO $pdo, int $userId, int $playedDelta, int $wonDelta, int $timeDeltaMs): void {
  get_or_create_stats($pdo, $userId); // ensure row exists
  $pdo->prepare("
    UPDATE game_stats
       SET games_played = games_played + ?,
           games_won    = games_won    + ?,
           time_played  = time_played  + ?
     WHERE user_id = ?
  ")->execute([$playedDelta, $wonDelta, $timeDeltaMs, $userId]);
}
