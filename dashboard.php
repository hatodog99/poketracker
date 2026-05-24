<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/connection.php';

$user_id = $_SESSION['user_id'];

$count_sql = "SELECT COUNT(*) AS total
              FROM pokemon
              WHERE user_id='$user_id'";

$count_result = mysqli_query($conn, $count_sql);

$count_row = mysqli_fetch_assoc($count_result);

$total_pokemon = $count_row['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<h1>Welcome,
<?php echo $_SESSION['username']; ?>!</h1>

<hr>

<h2>Total Pokémon:
<?php echo $total_pokemon; ?></h2>

<br>

<a href="add-pokemon.php">Add Pokémon</a>

<br><br>

<a href="collection.php">View Collection</a>

<br><br>

<a href="auth/logout.php"
    onclick="return confirm('Logout?')">
    Logout
</a>

</body>
</html>