<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/connection.php';

$id = $_GET['id'];

$user_id = $_SESSION['user_id'];

$get_sql = "SELECT image
            FROM pokemon
            WHERE id='$id'
            AND user_id='$user_id'";

$get_result = mysqli_query($conn, $get_sql);

$pokemon = mysqli_fetch_assoc($get_result);

if ($pokemon) {

    $image_path = "uploads/" . $pokemon['image'];

    if (file_exists($image_path)) {
        unlink($image_path);
    }

    $delete_sql = "DELETE FROM pokemon
                   WHERE id='$id'
                   AND user_id='$user_id'";

    mysqli_query($conn, $delete_sql);

}

header("Location: collection.php");
exit();
?>