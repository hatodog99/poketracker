<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/connection.php';
?>

<?php

if (isset($_POST['add_pokemon'])) {

    $user_id = $_SESSION['user_id'];

    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $level = trim($_POST['level']);
    $status = trim($_POST['status']);
    $rating = trim($_POST['rating']);

    if ($level < 1 || $level > 100) {
        die("Pokémon level must be between 1 and 100.");
    }

    if ($rating < 1 || $rating > 10) {
       die("Rating must be between 1 and 10.");
    }

    if ($_FILES['image']['size'] > 2000000) {
        die("Image size too large. 2MB max.");
    }

    // Image upload
    $image_name = time() . "_" . $_FILES['image']['name'];
    $temp_name = $_FILES['image']['tmp_name'];

    $folder = "uploads/" . $image_name;

    move_uploaded_file($temp_name, $folder);

    $image_type = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

    $allowed_types = ['jpg', 'jpeg', 'png'];

    if (!in_array($image_type, $allowed_types)) {
        die("Only JPG, JPEG, and PNG files are allowed.");
    }

    // Insert into database
    $sql = "INSERT INTO pokemon
            (user_id, name, type, level, status, rating, image)

            VALUES

            ('$user_id', '$name', '$type',
            '$level', '$status', '$rating', '$image_name')";

    if (mysqli_query($conn, $sql)) {
        echo "Pokémon Added Successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }        
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Pokémon</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="nav">
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="add-pokemon.php">Add Pokémon</a>
        <a href="auth/logout.php">Logout</a>
    </div>

    <hr>

    <h2>Add Pokémon</h2>

    <form method="POST" enctype="multipart/form-data">

        <input type="text"
            name="name"
            placeholder="Pokémon Name"
            required>

        <br><br>

        <select name="type" required>
            <option value="">Select Type</option>
            <option>Fire</option>
            <option>Water</option>
            <option>Grass</option>
            <option>Electric</option>
            <option>Psychic</option>
            <option>Dragon</option>
        </select>

        <br><br>

        <input type="number"
            name="level"
            placeholder="Level"
            required>

        <br><br>

        <select name="status">
            <option>Active</option>
            <option>Stored</option>
            <option>Favorite</option>
        </select>

        <br><br>

        <input type="number"
            name="rating"
            min="1"
            max="10"
            placeholder="Rating">

        <br><br>

        <input type="file"
            name="image"
            required>

        <br><br>

        <button type="submit" name="add_pokemon">
            Add Pokémon
        </button>

    </form>

</body>
</html>