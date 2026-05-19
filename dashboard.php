<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>

<a href="auth/logout.php">Logout</a>

<a href="add-pokemon.php">
    Add Pokémon
</a>

<br><br>

<a href="collection.php">
    View Collection
</a>

</body>
</html>