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
echo "Role: " . htmlspecialchars($user['role']) . "<br>";
echo "Username: " . htmlspecialchars($user['username']) . "<br>";
echo "Name: " . htmlspecialchars($user['name']) . "<br>";
echo "Email: " . htmlspecialchars($user['email']) . "<br>";
echo "Phone: " . htmlspecialchars($user['phone']) . "<br>";
// echo "Avatar: <img src='" . $user['avatar'] . "'><br>";
//get data from avatar and display it
$avatar = $user['avatar'];
if ($avatar !== null) {
    $data = file_get_contents($avatar);
    $base64 = 'data:image/' . pathinfo($avatar, PATHINFO_EXTENSION) . ';base64,' . base64_encode($data);
    echo "<img src='" . $base64 . "'><br>";
}
// button to GET chatBox
echo "<a href='chatBox.php?id=" . $_GET['id'] . "'>Chat</a><br>";
echo "---<br>";
//nếu là thông tin của chính user hoặc là teacher xem thông tin của student thì hiển thị nút edit
if ($curUser['id'] == $_GET['id'] || ($curUser['role'] === 'teacher' && $user['role'] === 'student')) {
    echo "<a href='editInfoUser.php?id=" . $_GET['id'] . "'>Edit</a>";
}
?>