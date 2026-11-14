<?php
require "db.php";
$result = $conn->query(
    "SELECT pl.product_id, pl.product_name, pl.description, pl.quantity, pl.price, pl.pic,
            data.store_name, data.logo
     FROM pl
     LEFT JOIN `data` ON pl.username = data.username
     ORDER BY pl.product_id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>My Store</h1>
    <p>
        <?php if (isset($_SESSION['username'])): ?>
            Welcome, <?= e($_SESSION['store_name']) ?> |
            <a href="mystore.php">Manage My Products</a> |
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="store_login.php">Login</a> |
            <a href="mystore_signup.php">Sign Up</a>
        <?php endif; ?>
    </p>
    <hr>

    <h2>Products</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Store</th>
                <th>Picture</th>
                <th>Name</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Price</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if ($row['logo']): ?>
                            <img class="logo" src="uploads/<?= e($row['logo']) ?>" alt="">
                        <?php endif; ?>
                        <?= e($row['store_name']) ?>
                    </td>
                    <td>
                        <?php if ($row['pic']): ?>
                            <img class="thumb" src="uploads/<?= e($row['pic']) ?>" alt="">
                        <?php else: ?>
                            No image
                        <?php endif; ?>
                    </td>
                    <td><?= e($row['product_name']) ?></td>
                    <td><?= e($row['description']) ?></td>
                    <td>
                        <?= ((int)$row['quantity'] > 0) ? (int)$row['quantity'] : 'Out of stock' ?>
                    </td>
                    <td><?= e(number_format((float)$row['price'], 2)) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No products available yet.</p>
    <?php endif; ?>
</body>
</html>