<?php
session_start();
if (!isset($_SESSION['user'])) {
    // go to login page if user is not logged in
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <!-- add CSS file -->
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="menu_style.css">
</head>
<body>

    <div class="navigation_bar">

        <!-- logout Button -->
        <form action="logout.php" method="post">
            <button class="nav_button" type="submit">Log out</button>
        </form>

    </div>

    <h1>Main Menu</h1>
    <p class="hello_user">Hello, <?php echo htmlspecialchars($_SESSION['user']); ?>!</p>
    
    <!-- main menu buttons -->
    <div class="main_buttons">
        <button class="menu_button" type="submit" onclick="window.location.href='gameIndex.html';">PLAY GAME</button>
        <button class="menu_button" type="submit" onclick="window.location.href='leaderboard.php';">LEADERBOARD</button>
        <button class="menu_button" type="submit" onclick="window.location.href='contact.html';">CONTACT</button>
        <button class="menu_button" type="submit" onclick="window.location.href='help.html';">HELP</button>
    </div>

</body>
</html>
