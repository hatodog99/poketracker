<?php
include '../includes/connection.php';

if (isset($_POST['register'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Encrypt password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password)
            VALUES ('$username', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
        echo "Registration Successful!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h2>Register</h2>

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

    <button type="submit" name="register">
        Register
    </button>

</form>

</body>
</html>