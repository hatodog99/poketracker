<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
include 'includes/connection.php';

$sys_message = "";
$msg_type = "";

if (isset($_POST['add_pokemon'])) {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $level = trim($_POST['level']);
    $status = trim($_POST['status']);
    $rating = trim($_POST['rating']);

    if ($level < 1 || $level > 100) {
        $sys_message = "Level must be between 1 and 100.";
        $msg_type = "error";
    } elseif ($rating < 1 || $rating > 10) {
        $sys_message = "Rating must be between 1 and 10.";
        $msg_type = "error";
    } elseif ($_FILES['image']['size'] > 2000000) {
        $sys_message = "Image size too large. 2MB max.";
        $msg_type = "error";
    } else {
        $image_name = time() . "_" . $_FILES['image']['name'];
        $temp_name = $_FILES['image']['tmp_name'];
        $image_type = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png'];

        if (!in_array($image_type, $allowed_types)) {
            $sys_message = "Only JPG, JPEG, and PNG files allowed.";
            $msg_type = "error";
        } else {
            $folder = "uploads/" . $image_name;
            move_uploaded_file($temp_name, $folder);

            $sql = "INSERT INTO pokemon (user_id, name, type, level, status, rating, image)
                    VALUES ('$user_id', '$name', '$type', '$level', '$status', '$rating', '$image_name')";

            if (mysqli_query($conn, $sql)) {
                $sys_message = "PKMN data registered to PC successfully!";
                $msg_type = "success";
            } else {
                $sys_message = "System Error: " . mysqli_error($conn);
                $msg_type = "error";
            }        
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Pokémon - PC System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .sys-message {
            background: var(--dialogue-bg); border: 4px solid var(--dialogue-border-outer);
            border-radius: 8px; box-shadow: inset 0 0 0 2px #ffffff, inset 0 0 0 4px var(--dialogue-border-inner), 4px 4px 0 rgba(0,0,0,0.15);
            max-width: 500px; margin: 0 auto 24px auto; padding: 16px 24px; font-size: 0.8rem; line-height: 1.6;
        }
        .sys-message.error { color: var(--gba-red); text-shadow: 1px 1px 0 #ffb0b0;}
        .sys-message.success { color: var(--gba-green); text-shadow: 1px 1px 0 #d0f0c0;}
        .form-title {
            margin-top: 0; margin-bottom: 24px; font-size: 1rem; text-align: center; color: var(--gba-text);
            text-shadow: 2px 2px 0 var(--gba-text-shadow); border-bottom: 4px dotted var(--dialogue-border-inner); padding-bottom: 16px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>PokéTracker</h1>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="dashboard.php">Trainer Card</a>
        <a href="collection.php">My PC Box</a>
        <a href="add-pokemon.php">Add PKMN</a>
        <a href="auth/logout.php" onclick="return confirm('Logout?')">Logout</a>
    </div>
</div>

<?php if ($sys_message != ""): ?>
    <div class="sys-message <?php echo $msg_type; ?>">
        ▶ <?php echo $sys_message; ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <h2 class="form-title">REGISTER PKMN</h2>

    <label>Pokémon Name:</label>
    <input type="text" name="name" placeholder="e.g. Pikachu" required>

    <label>Type:</label>
    <select name="type" required>
        <option value="">-- Select Type --</option>
        <option>Fire</option><option>Water</option><option>Grass</option>
        <option>Electric</option><option>Psychic</option><option>Dragon</option>
        <option>Normal</option><option>Flying</option><option>Fighting</option>
    </select>

    <label>Level (1-100):</label>
    <input type="number" name="level" placeholder="Lv." required>

    <label>Status:</label>
    <select name="status">
        <option>In Party</option><option>In PC Box</option><option>Daycare</option>
    </select>

    <label>Rating (1-10):</label>
    <input type="number" name="rating" min="1" max="10" placeholder="Score">

    <label>Sprite / Image:</label>
    <input type="file" name="image" required>

    <button type="submit" name="add_pokemon">SAVE TO PC</button>
</form>

</body>
</html>