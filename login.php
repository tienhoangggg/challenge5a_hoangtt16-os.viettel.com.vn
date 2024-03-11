<?php
require_once("database.php");
require_once("verifyJWT.php");
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
        //tạo JWT token và gắn vào cookie (id ad time)
        $jwt = createJWT(array("id" => $id, "time" => time()));
        //setcookie(name, value, expire, path, domain, secure, httponly);
        //chỉ sử dụng khi giao thức là https, same-site là strict
        setcookie("jwt", $jwt, [
            "expires" => time() + 3600,
            "path" => "/",
            "domain" => "",
            "secure" => true,
            "httponly" => true,
            "samesite" => "Strict"
        ]);
        // Chuyển hướng về trang chủ
        header("Location: index.php");
        exit();
    } else {
        echo "Sai tên đăng nhập hoặc mật khẩu";
    }
}
?>
