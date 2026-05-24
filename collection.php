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

$search = "";

if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$type_filter = "";

if (isset($_GET['type'])) {
    $type_filter = $_GET['type'];
}

$sql = "SELECT * FROM pokemon
        WHERE user_id='$user_id'
        AND name LIKE '%$search%'";

if ($type_filter != "") {
    $sql .= " AND type='$type_filter'";
}

$sql .= " ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Collection</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="nav">

        <a href="dashboard.php">Dashboard</a>

        <a href="add-pokemon.php">Add Pokémon</a>

        <a href="auth/logout.php">Logout</a>

    </div>

    <hr>

    <h1>My Pokémon Collection</h1>

    <form method="GET">

        <input type="text"
            name="search"
            placeholder="Search Pokémon"
            value="<?php echo $search; ?>">

        <button type="submit">
            Search
        </button>

    </form>

    <br>

    <form method="GET">

        <select name="type">

            <option value="">All Types</option>

            <option value="Fire">Fire</option>
            <option value="Water">Water</option>
            <option value="Grass">Grass</option>
            <option value="Electric">Electric</option>
            <option value="Psychic">Psychic</option>

        </select>

        <button type="submit">
            Filter
        </button>

    </form>

    <p>
    Total Results:
    <?php echo mysqli_num_rows($result); ?>
    </p>

    <div class="pokemon-container">

    <?php
    if (mysqli_num_rows($result) == 0) {
        echo "<h2>No Pokémon found.</h2>";
        echo "<p>Add your first Pokémon!</p>";
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

        <br><br>

        <a href="delete-pokemon.php?id=<?php echo $row['id']; ?>"
        onclick="return confirm('Release this Pokémon?')">
            Release
        </a>

    </div>

    <?php
    }
    ?>
    </div>

</body>
</html>