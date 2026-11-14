<?php
require "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: store_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $qry = $conn->prepare("SELECT pic FROM pl WHERE product_id = ? AND username = ?");
        $qry->bind_param("is", $id, $_SESSION['username']);
        $qry->execute();
        $row = $qry->get_result()->fetch_assoc();
        $qry->close();

        if ($row) {
            $qry = $conn->prepare("DELETE FROM pl WHERE product_id = ? AND username = ?");
            $qry->bind_param("is", $id, $_SESSION['username']);
            $qry->execute();
            $qry->close();

            delete_image($row['pic']);
            $_SESSION['msg'] = "Product deleted.";
        } else {
            $_SESSION['msg'] = "Could not delete product.";
        }
    }
}

header("Location: mystore.php");
exit;