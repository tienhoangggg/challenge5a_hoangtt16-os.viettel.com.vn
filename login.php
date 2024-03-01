<?php
require_once("database.php");
// phương thức GET, trả về form để đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    echo "<form method='post' action='login.php'>";
    echo "Username: <input type='text' name='username'><br>";
    echo "Password: <input type='password' name='password'><br>";
    echo "<input type='submit' value='Submit'>";
    echo "</form>";
}

// Kiểm tra xem form đã được gửi đi chưa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy thông tin từ form
    $username = $_POST["username"];
    $password = $_POST["password"];
    // Kiểm tra thông tin đăng nhập
    $id = check_login($username, $password);
    if ($id != -1) {
        //tạo JWT token và gắn vào cookie
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['id' => $id]);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        //chỉ sử dụng khi giao thức là https
        setcookie('jwt', $jwt, 0, '/', '', true, true);
        // Chuyển hướng về trang chủ
        header("Location: index.php");
        exit();
    } else {
        echo "Sai tên đăng nhập hoặc mật khẩu";
    }
}
?>
