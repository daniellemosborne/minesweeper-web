<?php
session_start();
require __DIR__ . '/db.php';   // provides $pdo (SQLite) + auto schema

// Require login (session contains user's email as 'user')
if (!isset($_SESSION['user'])) {
  header("Location: login.html");
  exit();
}
$userEmail = $_SESSION['user'];

// ---- Sorting (whitelist columns; ASC/DESC only) ----
$allowedSort = ['games_won', 'games_played', 'time_played'];
$sortColumn  = $_GET['sort']  ?? 'games_won';
$order       = $_GET['order'] ?? 'desc';

$sortColumn  = in_array($sortColumn, $allowedSort, true) ? $sortColumn : 'games_won';
$order       = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

// ---- Leaderboard: top 10 (users with/without stats) ----
$sql = "
  SELECT
    u.first_name,
    u.last_name,
    g.games_played,
    g.games_won,
    g.time_played
  FROM users u
  LEFT JOIN game_stats g ON u.id = g.user_id
  ORDER BY $sortColumn $order
  LIMIT 10
";
$leaders = $pdo->query($sql)->fetchAll();

// ---- Current user stats (may be NULLs if no stats row yet) ----
$st = $pdo->prepare("
  SELECT g.games_played, g.games_won, g.time_played
  FROM users u
  LEFT JOIN game_stats g ON u.id = g.user_id
  WHERE u.email = ?
  LIMIT 1
");
$st->execute([$userEmail]);
$userData = $st->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Leaderboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="menu_style.css"/>
</head>
<body>
  <div class="navigation_bar">
    <button class="nav_button" onclick="window.location.href='index.php';">Home</button>
    <form action="logout.php" method="post">
      <button class="nav_button" type="submit">Logout</button>
    </form>
  </div>

  <h1 class="leaderboard_header">Leaderboard</h1>

  <div class="form_container">
    <!-- sorting options -->
    <form method="get" action="leaderboard.php">
      <label for="sort">Sort By:</label>
      <select name="sort" id="sort">
        <option value="games_won"    <?= $sortColumn==='games_won'?'selected':''; ?>>Games Won</option>
        <option value="games_played" <?= $sortColumn==='games_played'?'selected':''; ?>>Games Played</option>
        <option value="time_played"  <?= $sortColumn==='time_played'?'selected':''; ?>>Time Played</option>
      </select>

      <label for="order">Order:</label>
      <select name="order" id="order">
        <option value="desc" <?= $order==='DESC'?'selected':''; ?>>Descending</option>
        <option value="asc"  <?= $order==='ASC'?'selected':'';  ?>>Ascending</option>
      </select>

      <button class="sort" type="submit">Sort</button>
    </form>

    <!-- top players table -->
    <table border="1">
      <thead>
        <tr>
          <th>Name</th>
          <th>Games Played</th>
          <th>Games Won</th>
          <th>Time Played (minutes)</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($leaders): ?>
          <?php foreach ($leaders as $row): ?>
            <?php
              $gp = (int)($row['games_played'] ?? 0);
              $gw = (int)($row['games_won']    ?? 0);
              $tp = (int)($row['time_played']  ?? 0);
            ?>
            <tr>
              <td><?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?></td>
              <td><?= $gp ?></td>
              <td><?= $gw ?></td>
              <td><?= round($tp / 60, 2) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4">No data available</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- logged in user stats -->
    <h3>Your Stats</h3>
    <?php
      $ugp = (int)($userData['games_played'] ?? 0);
      $ugw = (int)($userData['games_won']    ?? 0);
      $utp = (int)($userData['time_played']  ?? 0);
    ?>
    <?php if ($userData): ?>
      <p>Games Played: <?= $ugp ?></p>
      <p>Games Won: <?= $ugw ?></p>
      <p>Time Played: <?= round($utp / 60, 2) ?> minutes</p>
    <?php else: ?>
      <p>No stats available for your account</p>
    <?php endif; ?>
  </div>
</body>
</html>
