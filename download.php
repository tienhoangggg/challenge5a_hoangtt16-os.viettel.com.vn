<?php
require_once("verifyJWT.php");
require_once("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $curUser = verifyJWT();
    if ($curUser === null) {
        header("Location: login.php");
        exit();
    }
    $id_file = $_GET['id'];
    //get token jwt to verify that user has permission to download the file
    $jwt = $_COOKIE['jwt_' . str_replace('.', '_', $id_file)];
    $jwt = explode('.', $jwt);
    if (count($jwt) !== 3) {
        header("Location: login.php");
    }
    $signature = hash_hmac('sha256', $jwt[0] . "." . $jwt[1], JWT_key, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    if ($base64UrlSignature !== $jwt[2]) {
        header("Location: login.php");
    }
    $payload = json_decode(base64_decode($jwt[1]), true);
    $time = $payload['time'];
    if (time() - $time > 3600) {
        header("Location: login.php");
    }
    $id = $payload['id'];
    if ($id_file !== $id) {
        header("Location: login.php");
    }
    $file = STORAGE_DIR . $id_file;
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit();
    }
    else {
        echo "File not found";
    }
}
?>