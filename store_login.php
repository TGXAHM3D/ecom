<?php
require "db.php";

if (isset($_SESSION['username'])) {
    header("Location: mystore.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Email and password are required.";
    } else {
        $qry = $conn->prepare("SELECT username, store_name, password, logo FROM `data` WHERE email = ?");
        $qry->bind_param("s", $email);
        $qry->execute();
        $user = $qry->get_result()->fetch_assoc();
        $qry->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['username']   = $user['username'];
            $_SESSION['store_name'] = $user['store_name'];
            $_SESSION['logo']       = $user['logo'];
            header("Location: mystore.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Store Login</h2>
    <?php if ($error): ?><p style="color:red;"><?= e($error) ?></p><?php endif; ?>

    <form method="post" action="">
        <p>Email:<br><input type="email" name="email" required></p>
        <p>Password:<br><input type="password" name="password" required></p>
        <p><button type="submit">Login</button></p>
    </form>
    <p>No account? <a href="mystore_signup.php">Sign Up</a> | <a href="index.php">Home</a></p>
</body>
</html>