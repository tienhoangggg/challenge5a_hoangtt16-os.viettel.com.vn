<?php
require_once("database.php");
require_once("verifyJWT.php");

//phương thức GET, trả về form để edit thông tin user
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $curUser = verifyJWT();
    $user = get_user_info($_GET['id']);
    $user['id'] = $_GET['id'];
    //nếu không tìm thấy user thì chuyển hướng về trang chủ
    if ($user === null) {
        header("Location: index.php");
        exit();
    }
    //nếu không phải là thông tin của chính user hoặc là teacher xem thông tin của student thì không được edit
    if ($curUser['id'] != $user['id'] && ($curUser['role'] !== 'teacher' || $user['role'] !== 'student')) {
        header("Location: index.php");
        exit();
    }
    //nút quay lại infoUser
    echo "<a href='infoUser.php?id=" . $user['id'] . "'>Back</a><br>";
    echo "Role: " . $user['role'] . "<br>";
    //nếu là thông tin của chính user hoặc là teacher xem thông tin của student thì hiển thị form để edit
    echo "<form method='post' action='editInfoUser.php' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='id' value='" . $user['id'] . "'>";
    echo "Empty field if you don't want to change<br>";
    echo "New Username: <input type='text' name='username'><br>";
    echo "Name: <input type='text' name='name'><br>";
    echo "Email: <input type='text' name='email' value='" . $user['email'] . "'><br>";
    echo "Phone: <input type='text' name='phone' value='" . $user['phone'] . "'><br>";
    echo "New Password: <input type='password' name='password'><br>";
    echo "Avatar: <input type='file' name='avatar' accept='image/*'><br>";
    echo "<input type='submit' value='Submit'>";
    echo "</form>";
}

// Kiểm tra xem form đã được gửi đi chưa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //lấy thông tin từ form
    $user = verifyJWT();
    $id = $_POST["id"];
    if ($user['id'] != $id && ($user['role'] !== 'teacher' || get_role($id) !== 'student')) {
        header("Location: index.php");
        exit();
    }
    $username = $_POST["username"];
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $password = $_POST["password"];
    //handle file upload
    $avatar = '';
    if ($_FILES['avatar']['size'] > 0) {
        
        $target_file = STORAGE_DIR . hash('sha256', $id) . "." . pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file);
        $avatar = $target_file;
    }
    //hoặc user tự edit thông tin của mình, hoặc giáo viên edit thông tin của học sinh
    //nếu role là student thì không được edit username, name
    if ($user['role'] === 'student' && ($username !== '' || $name !== '')) {
        echo "You are not allowed to change username or name";
        return;
    }
    if(edit_user_info($id, $username, $name, $email, $phone, $avatar, $password)) {
        header("Location: infoUser.php?id=" . $id);
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>