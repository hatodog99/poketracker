<?php
session_start();

// If they are already logged in, redirect them back to the homepage
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Adjusted file path to go one folder up to include connection
include '../includes/connection.php';

$sys_message = "";
$msg_type = "";

if (isset($_POST['register'])) {
    
    // Safely sanitizes the inputs against SQL injections and quotes
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    if (empty($username) || empty($password)) {
        $sys_message = "All fields are required.";
        $msg_type = "error";
    } else {
        
        // 1. THE CHECK GATE: Look for existing users with this exact name
        $check_sql = "SELECT id FROM users WHERE username = '$username'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            // 2. THE INTERCEPTION: If row exists, stop execution and save a friendly error
            $sys_message = "This Trainer Name is already taken!";
            $msg_type = "error";
        } else {
            
            // 3. THE INSERTION: If name is unique, safely insert them into the database
            // Note: Stored as plain-text to maintain 100% compatibility with your current login.php query
            $insert_sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
            
            if (mysqli_query($conn, $insert_sql)) {
                $sys_message = "Account created! You can now log in.";
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
    <title>Trainer Registration - PC System</title>
    <!-- Relative path to stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Dynamic active page highlighting in auth subfolder -->
<?php $active_page = basename($_SERVER['PHP_SELF']); ?>
<div class="navbar" style="width: 100%; max-width: 1200px; margin: 24px auto;">
    <h1>PokéTracker</h1>
    <div class="nav-links">
        <a href="../index.php">Home</a>
        <a href="login.php" class="<?php echo $active_page == 'login.php' ? 'active' : ''; ?>">Login</a>
        <a href="register.php" class="<?php echo $active_page == 'register.php' ? 'active' : ''; ?>">Register</a>
    </div>
</div>

<div class="container">
    <!-- Dialogue box showing the retro warning messages -->
    <?php if ($sys_message != ""): ?>
        <div class="sys-message <?php echo $msg_type; ?>" style="max-width: 440px; margin: 0 auto 24px auto;">
            ▶ <?php echo $sys_message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" style="max-width: 440px; margin: 0 auto;">
        <h2 class="form-title">CREATE ACCOUNT</h2>

        <label>Trainer Username:</label>
        <input type="text" name="username" placeholder="Choose a name..." required autocomplete="off">

        <label>Password:</label>
        <input type="password" name="password" placeholder="Choose a password..." required>

        <button type="submit" name="register">REGISTER</button>
    </form>
</div>

</body>
</html>