<?php
require "db.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_name = trim($_POST['store_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm'] ?? '';
    $gender     = trim($_POST['gender'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');

    if ($store_name === '' || $username === '' || $email === '' || $password === ''
        || $gender === '' || $address === '' || $phone === '') {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT username FROM `data` WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Username or email is already registered.";
        } else {
            $logo = upload_image($_FILES['logo'] ?? null, 'logo', $error);

            if ($logo !== false) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $conn->prepare(
                    "INSERT INTO `data` (store_name, username, email, password, gender, address, phone, logo)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $insert->bind_param("ssssssss", $store_name, $username, $email, $hash, $gender, $address, $phone, $logo);
                $insert->execute();
                $insert->close();
                $success = "Account created! You can now log in.";
            }
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Store Sign Up</h2>
    <?php if ($error): ?><p style="color:red;"><?= e($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= e($success) ?></p><?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data">
        <p>Store Name:<br><input type="text" name="store_name" required></p>
        <p>Username:<br><input type="text" name="username" required></p>
        <p>Email:<br><input type="email" name="email" required></p>
        <p>Password:<br><input type="password" name="password" required></p>
        <p>Confirm Password:<br><input type="password" name="confirm" required></p>
        <p>Gender:<br>
            <select name="gender" required>
                <option value="">-- Select --</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </p>
        <p>Address:<br><textarea name="address" rows="2" cols="40" required></textarea></p>
        <p>Phone:<br><input type="text" name="phone" required></p>
        <p>Store Logo (JPG, PNG, GIF, WEBP, max 2 MB):<br>
            <input type="file" name="logo" accept="image/*"></p>
        <p><button type="submit">Sign Up</button></p>
    </form>
    <p>Already have an account? <a href="store_login.php">Login</a> | <a href="index.php">Home</a></p>
</body>
</html>