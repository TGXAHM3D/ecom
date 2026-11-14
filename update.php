<?php
require "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: store_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mystore.php");
    exit;
}

$id       = (int)($_POST['id'] ?? 0);
$name     = trim($_POST['product_name'] ?? '');
$desc     = trim($_POST['description'] ?? '');
$quantity = filter_var(trim($_POST['quantity'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$price    = trim($_POST['price'] ?? '');

if ($id <= 0) {
    header("Location: mystore.php");
    exit;
}

if ($name === '' || $desc === '' || $quantity === false || $price === '' || !is_numeric($price) || $price < 0) {
    $_SESSION['edit_error'] = "Name, description, a whole-number quantity and a valid price are required.";
    header("Location: edit.php?id=" . $id);
    exit;
}

$price = (float)$price;

$qry = $conn->prepare("SELECT pic FROM pl WHERE product_id = ? AND username = ?");
$qry->bind_param("is", $id, $_SESSION['username']);
$qry->execute();
$current = $qry->get_result()->fetch_assoc();
$qry->close();

if (!$current) {
    $_SESSION['msg'] = "Product not found.";
    header("Location: mystore.php");
    exit;
}

$pic = $current['pic'];
$error = "";
$newPic = upload_image($_FILES['pic'] ?? null, 'product', $error);

if ($newPic === false) {
    $_SESSION['edit_error'] = $error;
    header("Location: edit.php?id=" . $id);
    exit;
}

if ($newPic !== null) {
    delete_image($pic);
    $pic = $newPic;
}

$qry = $conn->prepare(
    "UPDATE pl SET product_name = ?, description = ?, quantity = ?, price = ?, pic = ?
     WHERE product_id = ? AND username = ?"
);
$qry->bind_param("ssidsis", $name, $desc, $quantity, $price, $pic, $id, $_SESSION['username']);
$qry->execute();
$qry->close();

$_SESSION['msg'] = "Product updated successfully.";
header("Location: mystore.php");
exit;