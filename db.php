<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli("localhost", "root", "", "commerce");   // use YOUR database name
$conn->set_charset("utf8mb4");

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/*
 * Returns: null = no file chosen, false = error (message in $error), string = saved file name
 */
function upload_image($file, $prefix, &$error) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Image upload failed. Please try again.";
        return false;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        $error = "Image must be smaller than 2 MB.";
        return false;
    }

    $info = @getimagesize($file['tmp_name']);
    $allowed = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WEBP => 'webp'
    ];
    if ($info === false || !isset($allowed[$info[2]])) {
        $error = "Only JPG, PNG, GIF or WEBP images are allowed.";
        return false;
    }

    $dir = __DIR__ . '/uploads';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $name = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $allowed[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        $error = "Could not save the uploaded image.";
        return false;
    }
    return $name;
}

function delete_image($name) {
    if ($name) {
        $path = __DIR__ . '/uploads/' . basename($name);
        if (is_file($path)) {
            unlink($path);
        }
    }
}