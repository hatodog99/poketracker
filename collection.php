<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/connection.php';
?>

<?php

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM pokemon
        WHERE user_id = '$user_id'
        ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Collection</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <a href="dashboard.php">Dashboard</a>

    <a href="add-pokemon.php">Add Pokémon</a>

    <a href="auth/logout.php">Logout</a>

    <hr>

    <h1>My Pokémon Collection</h1>

    <div class="pokemon-container">

    <?php
    if (mysqli_num_rows($result) == 0) {
        echo "<p>No Pokémon added yet!</p>";
    }
    ?>

    <?php
    while ($row = mysqli_fetch_assoc($result)) {
    ?>

    <div class="pokemon-card">

        <img src="uploads/<?php echo $row['image']; ?>">

        <h2><?php echo $row['name']; ?></h2>

        <p>Type: <?php echo $row['type']; ?></p>

        <p>Level: <?php echo $row['level']; ?></p>

        <p>Status: <?php echo $row['status']; ?></p>

        <p>Rating: <?php echo $row['rating']; ?>/10</p>

        <a href="edit-pokemon.php?id=<?php echo $row['id']; ?>">
            Edit
        </a>

    </div>

    <?php
    }
    ?>
    </div>

</body>
</html>