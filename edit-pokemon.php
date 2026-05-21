<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/connection.php';

$id = $_GET['id'];

$sql = "SELECT * FROM pokemon
        WHERE id='$id'
        AND user_id='{$_SESSION['user_id']}'";

$result = mysqli_query($conn, $sql);

$pokemon = mysqli_fetch_assoc($result);

if (!$pokemon) {
    die("Pokémon not found.");
}

?>

<?php

if (isset($_POST['update_pokemon'])) {

    $name = $_POST['name'];
    $type = $_POST['type'];
    $level = $_POST['level'];
    $status = $_POST['status'];
    $rating = $_POST['rating'];

    $update_sql = "UPDATE pokemon SET
                   name='$name',
                   type='$type',
                   level='$level',
                   status='$status',
                   rating='$rating'

                   WHERE id='$id'";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: collection.php");
        echo "<p>Pokémon updated successfully!</p>";
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Pokémon</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<h2>Edit Pokémon</h2>

<form method="POST">

    <input type="text"
           name="name"
           value="<?php echo $pokemon['name']; ?>"
           required>

    <br><br>

    <input type="text"
           name="type"
           value="<?php echo $pokemon['type']; ?>"
           required>

    <br><br>

    <input type="number"
           name="level"
           value="<?php echo $pokemon['level']; ?>"
           required>

    <br><br>

    <input type="text"
           name="status"
           value="<?php echo $pokemon['status']; ?>"
           required>

    <br><br>

    <input type="number"
           name="rating"
           value="<?php echo $pokemon['rating']; ?>"
           required>

    <br><br>

    <button type="submit" name="update_pokemon">
        Update Pokémon
    </button>

</form>

</body>
</html>