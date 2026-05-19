<?php
session_start();

include '../includes/connection.php';

if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users
            WHERE username='$username'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        // Verify password
        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            header("Location: ../dashboard.php");
            exit();

        } else {
            echo "Invalid Password!";
        }

    } else {
        echo "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h2>Login</h2>

<form method="POST">

    <input type="text"
           name="username"
           placeholder="Username"
           required>

    <br><br>

    <input type="password"
           name="password"
           placeholder="Password"
           required>

    <br><br>

    <button type="submit" name="login">
        Login
    </button>

</form>

</body>
</html>