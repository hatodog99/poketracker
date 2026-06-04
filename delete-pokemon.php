<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/connection.php';

$id = mysqli_real_escape_string($conn, $_GET['id']);
$user_id = $_SESSION['user_id'];

// 1. Fetch all 5 album photo filenames to clean up files on the server
$img_query = mysqli_query($conn, "SELECT image1, image2, image3, image4, image5 FROM pokemon WHERE id='$id' AND user_id='$user_id'");

if ($row = mysqli_fetch_assoc($img_query)) {
    // Loop through all 5 slots and delete existing files from the uploads/ directory
    for ($i = 1; $i <= 5; $i++) {
        $img_key = 'image' . $i;
        $file_path = "uploads/" . $row[$img_key];
        
        if ($row[$img_key] != '' && file_exists($file_path)) {
            unlink($file_path); // Deletes the actual image file from your uploads folder
        }
    }
}

// 2. Delete the record from the database
$sql = "DELETE FROM pokemon WHERE id='$id' AND user_id='$user_id'";

if (mysqli_query($conn, $sql)) {
    // Redirect smoothly back to the PC Box
    header("Location: collection.php");
    exit();
} else {
    die("Error deleting Pokémon: " . mysqli_error($conn));
}
?>