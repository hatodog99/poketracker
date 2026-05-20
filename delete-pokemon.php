<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/connection.php';

$id = $_GET['id'];

$sql = "DELETE FROM pokemon
        WHERE id='$id'";

if (mysqli_query($conn, $sql)) {

    header("Location: collection.php");
    exit();

} else {

    echo "Error: " . mysqli_error($conn);

}
?>

<a href="edit-pokemon.php?id=<?php echo $row['id']; ?>">
    Edit
</a>

<br><br>

<a href="delete-pokemon.php?id=<?php echo $row['id']; ?>"
   onclick="return confirm('Delete this Pokémon?')">
    Delete
</a>