<?php
session_start();
include '../includes/connection.php';

$sys_message = "";
$msg_type = "";

if (isset($_GET['registered']) && $_GET['registered'] == 'success') {
    $sys_message = "Registration successful! You may now log in.";
    $msg_type = "success";
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../index.php");
            exit();
        } else {
            $sys_message = "Invalid Password!";
            $msg_type = "error";
        }
    } else {
        $sys_message = "Trainer not found!";
        $msg_type = "error";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trainer Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <a href="../index.php">Home</a>
        <a href="register.php">Register</a>
    </div>
</div>

<?php if ($sys_message != ""): ?>
    <div class="sys-message <?php echo $msg_type; ?>">
        ▶ <?php echo $sys_message; ?>
    </div>
<?php endif; ?>

<form method="POST">
    <h2 class="form-title">TRAINER LOGIN</h2>
    <label>Username:</label>
    <input type="text" name="username" placeholder="Enter Username" required>
    <label>Password:</label>
    <input type="password" name="password" placeholder="Enter Password" required>
    <button type="submit" name="login">ACCESS PC</button>
</form>

</body>
</html>