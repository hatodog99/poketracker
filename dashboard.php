<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
include 'includes/connection.php';
$user_id = $_SESSION['user_id'];

$count_sql = "SELECT COUNT(*) AS total FROM pokemon WHERE user_id='$user_id'";
$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total_pokemon = $count_row['total'];

$trainer_id = str_pad($user_id * 143, 5, "0", STR_PAD_LEFT); 
$started_date = date("M. d, Y");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trainer Card</title>
    <link rel="stylesheet" href="assets/css/style.css">
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

<div class="trainer-card">
    <div class="tc-header">
        <span class="tc-title">TRAINER CARD</span>
    </div>
    
    <div class="tc-body">
        <div class="tc-row">
            <span class="tc-label">Name</span>
            <span class="tc-value tc-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
        <div class="tc-row">
            <span class="tc-label">ID No.</span>
            <span class="tc-value"><?php echo $trainer_id; ?></span>
        </div>
        
        <div class="tc-stats">
            <div class="tc-row">
                <span class="tc-label">Pokédex</span>
                <span class="tc-value"><?php echo $total_pokemon; ?> / <?php echo $total_pokemon; ?></span>
            </div>
            <div class="tc-row">
                <span class="tc-label">Time</span>
                <span class="tc-value">99:59</span>
            </div>
            <div class="tc-row">
                <span class="tc-label">Started</span>
                <span class="tc-value"><?php echo $started_date; ?></span>
            </div>
        </div>
    </div>
</div>

</body>
</html>