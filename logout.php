<?php
session_start();

// unset all session vars
$_SESSION = [];

// delete the session cookie
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000, 
    $params["path"], $params["domain"], 
    $params["secure"], $params["httponly"]
  );
}

// destroy the session on the server
session_destroy();

// regenerate a fresh session ID to avoid reuse
session_start();
session_regenerate_id(true);

// redirect to login page
header("Location: login.html");
exit;
