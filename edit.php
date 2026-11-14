<?php
require "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: store_login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: mystore.php");
    exit;
}

$qry = $conn->prepare(
    "SELECT product_id, product_name, description, quantity, price, pic
     FROM pl WHERE product_id = ? AND username = ?"
);
$qry->bind_param("is", $id, $_SESSION['username']);
$qry->execute();
$product = $qry->get_result()->fetch_assoc();
$qry->close();

if (!$product) {
    $_SESSION['msg'] = "Product not found.";
    header("Location: mystore.php");
    exit;
}

$error = $_SESSION['edit_error'] ?? "";
unset($_SESSION['edit_error']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Edit Product</h2>
    <?php if ($error): ?><p style="color:red;"><?= e($error) ?></p><?php endif; ?>

    <form method="post" action="update.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= (int)$product['product_id'] ?>">
        <p>Product Name:<br>
            <input type="text" name="product_name" value="<?= e($product['product_name']) ?>" required></p>
        <p>Description:<br>
            <textarea name="description" rows="3" cols="40" required><?= e($product['description']) ?></textarea></p>
        <p>Quantity:<br>
            <input type="number" name="quantity" min="0" step="1" value="<?= (int)$product['quantity'] ?>" required></p>
        <p>Price:<br>
            <input type="number" name="price" step="0.01" min="0" value="<?= e($product['price']) ?>" required></p>
        <p>Current Picture:<br>
            <?php if ($product['pic']): ?>
                <img class="thumb" src="uploads/<?= e($product['pic']) ?>" alt="">
            <?php else: ?>
                No image
            <?php endif; ?>
        </p>
        <p>Replace Picture (leave empty to keep the current one):<br>
            <input type="file" name="pic" accept="image/*"></p>
        <p><button type="submit">Update Product</button></p>
    </form>
    <p><a href="mystore.php">Cancel</a></p>
</body>
</html>