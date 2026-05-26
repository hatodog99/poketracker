<?php
session_start();

include 'includes/connection.php';

$sql = "SELECT pokemon.*, users.username
        FROM pokemon

        JOIN users
        ON pokemon.user_id = users.id

        ORDER BY pokemon.created_at DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>PokéTracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="index-page">

<div class="navbar">
    <h1>PokéTracker Community</h1>

    <div class="nav-links">

    <?php if (isset($_SESSION['user_id'])) { ?>

        <a href="dashboard.php" class="username-link">
            <?php echo $_SESSION['username']; ?>
        </a>

        <a href="dashboard.php">Dashboard</a>

        <a href="collection.php">My Collection</a>

        <a href="auth/logout.php"
            onclick="return confirm('Logout?')">
            Logout
        </a>

    <?php } else { ?>

        <a href="auth/login.php">Login</a>

        <a href="auth/register.php">Register</a>

    <?php } ?>

    </div>
</div>

<div class="container">

    <h2>Recent Pokémon Collections</h2>

    <div class="pokemon-container">

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

            <div class="pokemon-card">

                <img src="uploads/<?php echo $row['image']; ?>">

                <h2><?php echo $row['name']; ?></h2>

                <p>Trainer:
                <?php echo $row['username']; ?></p>

                <p>Type:
                <?php echo $row['type']; ?></p>

                <p>Level:
                <?php echo $row['level']; ?></p>

                <p>Status:
                <?php echo $row['status']; ?></p>

                <p>Rating:
                <?php echo $row['rating']; ?>/10</p>

            </div>

        <?php } ?>

    </div>

</div>

</body>
</html>