<?php
include '../includes/connection.php';

$sys_message = "";
$msg_type = "";

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
        header("Location: login.php?registered=success");
        exit();
    } else {
        $sys_message = "System Error: " . mysqli_error($conn);
        $msg_type = "error";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>New Trainer Registration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .sys-message {
            background: var(--dialogue-bg); border: 4px solid var(--dialogue-border-outer);
            border-radius: 8px; box-shadow: inset 0 0 0 2px #ffffff, inset 0 0 0 4px var(--dialogue-border-inner), 4px 4px 0 rgba(0,0,0,0.15);
            max-width: 500px; margin: 0 auto 24px auto; padding: 16px 24px; font-size: 0.8rem; line-height: 1.6;
        }
        .sys-message.error { color: var(--gba-red); text-shadow: 1px 1px 0 #ffb0b0;}
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
        <a href="../index.php">Home</a>
        <a href="login.php">Login</a>
    </div>
</div>

<?php if ($sys_message != ""): ?>
    <div class="sys-message <?php echo $msg_type; ?>">
        ▶ <?php echo $sys_message; ?>
    </div>
<?php endif; ?>

<form method="POST">
    <h2 class="form-title">NEW TRAINER REGISTRATION</h2>
    <label>Choose a Username:</label>
    <input type="text" name="username" placeholder="Username" required>
    <label>Choose a Password:</label>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="register">REGISTER TRAINER</button>
</form>

</body>
</html>