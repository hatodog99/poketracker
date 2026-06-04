<?php
session_start();

// GATED ACCESS: Users must have an account and be logged in to view public profiles!
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/connection.php';

if (!isset($_GET['username'])) {
    header("Location: index.php");
    exit();
}

$username = mysqli_real_escape_string($conn, $_GET['username']);

// 1. Fetch target user's custom details
$user_query = "SELECT id, card_theme, sticker_id, bio FROM users WHERE username='$username'";
$user_result = mysqli_query($conn, $user_query);

if (mysqli_num_rows($user_result) == 0) {
    die("Trainer not found.");
}

$user_row = mysqli_fetch_assoc($user_result);
$target_user_id = $user_row['id'];
$card_theme = $user_row['card_theme'] ?: 'blue'; 
$sticker_id = $user_row['sticker_id'] ?: 0;
$bio = $user_row['bio'] ?: '';

// 2. Count how many Pokémon this trainer has caught
$count_sql = "SELECT COUNT(*) AS total FROM pokemon WHERE user_id='$target_user_id'";
$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total_pokemon = $count_row['total'];

$trainer_id = str_pad($target_user_id * 143, 5, "0", STR_PAD_LEFT); 
$started_date = date("M. d, Y");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trainer Profile - <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php $active_page = basename($_SERVER['PHP_SELF']); ?>
<div class="navbar">
    <h1>PokéTracker</h1>
    <div class="nav-links">
        <a href="index.php" class="<?php echo $active_page == 'index.php' ? 'active' : ''; ?>">Home</a>
        <?php if (isset($_SESSION['user_id'])) { ?>
            <a href="dashboard.php" class="<?php echo $active_page == 'dashboard.php' ? 'active' : ''; ?>">Trainer Card</a>
            <a href="collection.php" class="<?php echo $active_page == 'collection.php' ? 'active' : ''; ?>">My PC Box</a>
            <a href="add-pokemon.php" class="<?php echo $active_page == 'add-pokemon.php' ? 'active' : ''; ?>">Add PKMN</a>
            <a href="leaderboard.php" class="<?php echo $active_page == 'leaderboard.php' ? 'active' : ''; ?>">Leaderboard</a>
            <a href="auth/logout.php" onclick="return confirm('Logout?')">Logout</a>
        <?php } else { ?>
            <a href="auth/login.php">Login</a>
            <a href="auth/register.php">Register</a>
        <?php } ?>
    </div>
</div>

<div class="container">
    <h2 style="text-align: center; text-transform: uppercase; margin: 40px 0 20px 0; text-shadow: 2px 2px 0 var(--gba-text-shadow);"><?php echo htmlspecialchars($username); ?>'s Profile</h2>
    
    <!-- Trainer Card -->
    <div class="trainer-card theme-<?php echo $card_theme; ?>">
        <div class="tc-header">
            <span class="tc-title">TRAINER CARD</span>
        </div>
        
        <div class="tc-body">
            <div class="tc-row">
                <span class="tc-label">Name</span>
                <span class="tc-value tc-name"><?php echo htmlspecialchars($username); ?></span>
            </div>
            <div class="tc-row">
                <span class="tc-label">ID No.</span>
                <span class="tc-value"><?php echo $trainer_id; ?></span>
            </div>
            <div class="tc-row">
                <span class="tc-label">Pokédex</span>
                <span class="tc-value"><?php echo $total_pokemon; ?></span>
            </div>
            <div class="tc-row" style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;">
                <span class="tc-label">Started</span>
                <span class="tc-value"><?php echo $started_date; ?></span>
            </div>

            <!-- Polished GBA Signature Plaque Box -->
            <div class="tc-bio-row">
                <span class="tc-bio-label">Signature:</span>
                <span class="tc-bio-text"><?php echo htmlspecialchars($bio ?: 'None'); ?></span>
            </div>

            <!-- Custom Sticker -->
            <?php if ($sticker_id > 0) { ?>
                <img src="assets/sprites/<?php echo $sticker_id; ?>.png" class="trainer-sprite" alt="Sticker">
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>