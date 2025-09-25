<?php
session_start();
require __DIR__ . '/db.php';  
header('Content-Type: application/json');

try {
  // must be logged in
  if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
  }
  $userId = (int)$_SESSION['user_id'];

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST allowed.']);
    exit;
  }

  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON body.']);
    exit;
  }

  $wonRaw       = $data['won']        ?? null;
  $timePlayedRaw= $data['timePlayed'] ?? null;

  if ($wonRaw === null || $timePlayedRaw === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing fields: won, timePlayed']);
    exit;
  }

  $won = is_bool($wonRaw) ? $wonRaw : filter_var($wonRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
  $timePlayed = filter_var($timePlayedRaw, FILTER_VALIDATE_INT);
  if ($won === null || $timePlayed === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid won/timePlayed values']);
    exit;
  }
  if ($timePlayed < 0) $timePlayed = 0;

  // ensure stats row exists, then update
  get_or_create_stats($pdo, $userId);
  update_stats($pdo, $userId, /*playedDelta*/1, /*wonDelta*/($won ? 1 : 0), /*timeDeltaMs*/ $timePlayed);

  echo json_encode(['success' => true, 'message' => 'Stats updated successfully.']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}
