<?php
require_once("config.php");
require_once("database.php");
// verify JWT (authorization), if not valid, redirect to login page
function verifyJWT() {
    if (!isset($_COOKIE['jwt'])) {
        header("Location: login.php");
    }
    $jwt = $_COOKIE['jwt'];
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
    $id = $payload['id'];
    //user includes username, role
    $role = get_role($id);
    $user['role'] = $role;
    $user['id'] = $id;
    if ($user === null) {
        header("Location: login.php");
    }
    return $user;
}
?>