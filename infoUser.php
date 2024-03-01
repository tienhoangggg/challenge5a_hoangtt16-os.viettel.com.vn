<?php
require_once("verifyJWT.php");

//tránh người ngoài không có quyền truy cập vào trang này
$curUser = verifyJWT();

// giao diện hiển thị thông tin user
$user = get_user_info($_GET['id']);
//nếu không tìm thấy user thì chuyển hướng về trang chủ
if ($user === null) {
    header("Location: index.php");
    exit();
}
//nút quay lại trang chủ
echo "<a href='index.php'>Back</a><br>";

//hiển thị thông tin user
echo "Role: " . $user['role'] . "<br>";
echo "Username: " . $user['username'] . "<br>";
echo "Name: " . $user['name'] . "<br>";
echo "Email: " . $user['email'] . "<br>";
echo "Phone: " . $user['phone'] . "<br>";
echo "Avatar: <img src='" . $user['avatar'] . "'><br>";
// button to GET chatBox
echo "<a href='chatBox.php?id=" . $_GET['id'] . "'>Chat</a><br>";
echo "---<br>";
//nếu là thông tin của chính user hoặc là teacher xem thông tin của student thì hiển thị nút edit
if ($curUser['id'] == $_GET['id'] || ($curUser['role'] === 'teacher' && $user['role'] === 'student')) {
    echo "<a href='editInfoUser.php?id=" . $_GET['id'] . "'>Edit</a>";
}
?>