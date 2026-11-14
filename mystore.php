<?php
require "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: store_login.php");
    exit;
}

$username = $_SESSION['username'];
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['product_name'] ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $quantity = filter_var(trim($_POST['quantity'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $price    = trim($_POST['price'] ?? '');

    if ($name === '' || $desc === '' || $price === '') {
        $error = "Name, description and price are required.";
    } elseif ($quantity === false) {
        $error = "Quantity must be a whole number (0 or more).";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Price must be a valid positive number.";
    } else {
        $price = (float)$price;
        $pic = upload_image($_FILES['pic'] ?? null, 'product', $error);

        if ($pic !== false) {
            $qry = $conn->prepare(
                "INSERT INTO pl (username, product_name, description, quantity, price, pic)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $qry->bind_param("sssids", $username, $name, $desc, $quantity, $price, $pic);
            $qry->execute();
            $qry->close();
            $_SESSION['msg'] = "Product added successfully.";
            header("Location: mystore.php");
            exit;
        }
    }
}

$msg = $_SESSION['msg'] ?? "";
unset($_SESSION['msg']);

$list = $conn->prepare(
    "SELECT product_id, product_name, description, quantity, price, pic
     FROM pl WHERE username = ? ORDER BY product_id DESC"
);
$list->bind_param("s", $username);
$list->execute();
$result = $list->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Manage Products</h1>
    <p>
        <?php if (!empty($_SESSION['logo'])): ?>
            <img class="logo" src="uploads/<?= e($_SESSION['logo']) ?>" alt="Logo">
        <?php endif; ?>
        Logged in as <?= e($_SESSION['store_name']) ?> (<?= e($username) ?>) |
        <a href="index.php">View Store</a> |
        <a href="logout.php">Logout</a>
    </p>
    <hr>

    <?php if ($msg): ?><p style="color:green;"><?= e($msg) ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?= e($error) ?></p><?php endif; ?>

    <h2>Add New Product</h2>
    <form method="post" action="mystore.php" enctype="multipart/form-data">
        <p>Product Name:<br><input type="text" name="product_name" required></p>
        <p>Description:<br><textarea name="description" rows="3" cols="40" required></textarea></p>
        <p>Quantity:<br><input type="number" name="quantity" min="0" step="1" value="1" required></p>
        <p>Price:<br><input type="number" name="price" step="0.01" min="0" required></p>
        <p>Product Picture (JPG, PNG, GIF, WEBP, max 2 MB):<br>
            <input type="file" name="pic" accept="image/*"></p>
        <p><button type="submit">Upload Product</button></p>
    </form>

    <h2>My Products</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Picture</th>
                <th>Name</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$row['product_id'] ?></td>
                    <td>
                        <?php if ($row['pic']): ?>
                            <img class="thumb" src="uploads/<?= e($row['pic']) ?>" alt="">
                        <?php else: ?>
                            No image
                        <?php endif; ?>
                    </td>
                    <td><?= e($row['product_name']) ?></td>
                    <td><?= e($row['description']) ?></td>
                    <td><?= (int)$row['quantity'] ?></td>
                    <td><?= e(number_format((float)$row['price'], 2)) ?></td>
                    <td>
                        <a href="edit.php?id=<?= (int)$row['product_id'] ?>">Edit</a>
                        <form method="post" action="delete.php" style="display:inline;"
                              onsubmit="return confirm('Delete this product?');">
                            <input type="hidden" name="id" value="<?= (int)$row['product_id'] ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>You have no products yet.</p>
    <?php endif; ?>
</body>
</html>