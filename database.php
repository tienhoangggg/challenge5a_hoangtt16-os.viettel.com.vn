<?php
require_once("config.php");
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);
// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// sử dụng id21925875_database
$sql = "USE " . DB_NAME;
if ($conn->query($sql) === FALSE) {
    echo "Error changing database: " . $conn->error;
}

// function check login
function check_login($username, $password) {
    global $conn;
    $password = hash('sha256', $password);
    $sql = $conn->prepare("SELECT id FROM users WHERE username = ? AND password = ?");
    $sql->bind_param("ss", $username, $password);
    $sql->execute();
    $result = $sql->get_result();
    //lấy id của user nếu đăng nhập thành công
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['id'];
    } else {
        return -1;
    }
}

// function get user info (except password)
function get_user_info($id) {
    global $conn;
    $sql = $conn->prepare("SELECT role, username, name, email, phone, avatar FROM users WHERE id = ?");
    $sql->bind_param("i", $id);
    $sql->execute();
    $result = $sql->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// edit user info
function edit_user_info($id, $username, $name, $email, $phone, $avatar, $password) {
    global $conn;
    //if any field is empty, don't update that field
    $sql = "UPDATE users SET ";
    $type = "";
    $param = array();
    if ($username !== "") {
        $sql .= "username = ?, ";
        $type .= "s";
        array_push($param, $username);
    }
    if ($name !== "") {
        $sql .= "name = ?, ";
        $type .= "s";
        array_push($param, $name);
    }
    if ($email !== "") {
        $sql .= "email = ?, ";
        $type .= "s";
        array_push($param, $email);
    }
    if ($phone !== "") {
        $sql .= "phone = ?, ";
        $type .= "s";
        array_push($param, $phone);
    }
    if ($avatar !== "") {
        $sql .= "avatar = ?, ";
        $type .= "s";
        array_push($param, $avatar);
    }
    if ($password !== "") {
        $password = hash('sha256', $password);
        $sql .= "password = ?, ";
        $type .= "s";
        array_push($param, $password);
    }
    $sql = rtrim($sql, ", ");
    $sql .= " WHERE id = ?";
    $type .= "i";
    array_push($param, $id);
    $sql = $conn->prepare($sql);
    $sql->bind_param($type, ...$param);
    if ($sql->execute() === TRUE) {
        return true;
    } else {
        return false;
    }
}

// function get role
function get_role($id) {
    global $conn;
    $sql = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $sql->bind_param("i", $id);
    $sql->execute();
    $result = $sql->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['role'];
    } else {
        return null;
    }
}

//get role, username of all users
function get_all_users() {
    global $conn;
    $sql = $conn->prepare("SELECT id, role, username FROM users");
    $sql->execute();
    $result = $sql->get_result();
    $users = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            array_push($users, $row);
        }
    }
    return $users;
}

// function get chat
function get_chat($id1, $id2) {
    global $conn;
    $sql = $conn->prepare("SELECT id, sender, (SELECT username FROM users WHERE users.id = sender) AS username, message FROM chat WHERE (sender = ? AND receiver = ?) OR (sender = ? AND receiver = ?) ORDER BY created_at");
    $sql->bind_param("iiii", $id1, $id2, $id2, $id1);
    $sql->execute();
    $result = $sql->get_result();
    $chat = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            array_push($chat, $row);
        }
    }
    return $chat;
}

// function send message
function send_message($sender, $receiver, $message) {
    global $conn;
    $sql = $conn->prepare("INSERT INTO chat (sender, receiver, message) VALUES (?, ?, ?)");
    $sql->bind_param("iis", $sender, $receiver, $message);
    if ($sql->execute() === TRUE) {
        return true;
    } else {
        return false;
    }
}

// function delete message
function delete_message($id_user, $id) {
    global $conn;
    $sql = $conn->prepare("DELETE FROM chat WHERE id = ? AND sender = ?");
    $sql->bind_param("ii", $id, $id_user);
    if ($sql->execute() === TRUE) {
        return true;
    } else {
        return false;
    }
}

// function edit message
function edit_message($id_user, $id, $message) {
    global $conn;
    $sql = $conn->prepare("UPDATE chat SET message = ? WHERE id = ? AND sender = ?");
    $sql->bind_param("sii", $message, $id, $id_user);
    if ($sql->execute() === TRUE) {
        return true;
    } else {
        return false;
    }
}

// function teacher upload assignment
function teacher_upload_assignment($id_user, $title, $description, $file) {
    global $conn;
    $sql = $conn->prepare("INSERT INTO assignments (teacher, title, description, file) VALUES (?, ?, ?, ?)");
    $sql->bind_param("isss", $id_user, $title, $description, $file);
    if ($sql->execute() === TRUE) {
        return true;
    } else {
        return false;
    }
}

// function student upload assignment
function student_upload_assignment($id_user, $assignment_id, $file) {
    global $conn;
    $sql = $conn->prepare("INSERT INTO submissions (student, assignment_id, file) VALUES (?, ?, ?)");
    $sql->bind_param("iis", $id_user, $assignment_id, $file);
    if ($sql->execute() === TRUE) {
        return true;
    } else {
        return false;
    }
}

// function get all assignments
function get_all_assignments() {
    global $conn;
    $sql = $conn->prepare("SELECT id, (SELECT username FROM users WHERE users.id = teacher) AS poster, title FROM assignments");
    $sql->execute();
    $result = $sql->get_result();
    $assignments = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            array_push($assignments, $row);
        }
    }
    return $assignments;
}

// function get assignment detail
function get_assignment_detail($id) {
    global $conn;
    $sql = $conn->prepare("SELECT (SELECT username FROM users WHERE users.id = teacher) AS poster, title, description, file FROM assignments WHERE id = ?");
    $sql->bind_param("i", $id);
    $sql->execute();
    $result = $sql->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// function get all submissions
function get_all_submissions($id_user, $assignment_id) {
    global $conn;
    $sql = $conn->prepare("SELECT (SELECT username FROM users WHERE users.id = student) AS poster, file, created_at FROM submissions WHERE assignment_id = ?");
    if (get_role($id_user) != "teacher") {
        $sql .= " AND student = ?";
        $sql->bind_param("ii", $assignment_id, $id_user);
    }
    else {
        $sql->bind_param("i", $assignment_id);
    }
    $sql->execute();
    $result = $sql->get_result();
    $submissions = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            array_push($submissions, $row);
        }
    }
    return $submissions;
}

// function get all riddles
function get_all_riddles() {
    global $conn;
    $sql = $conn->prepare("SELECT id, title, (SELECT username FROM users WHERE users.id = teacher) AS poster FROM riddles");
    $sql->execute();
    $result = $sql->get_result();
    $riddles = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            array_push($riddles, $row);
        }
    }
    return $riddles;
}

// function get riddle detail
function get_riddle_detail($id) {
    global $conn;
    $sql = $conn->prepare("SELECT title, teacher, (SELECT username FROM users WHERE users.id = teacher) AS poster, description FROM riddles WHERE id = ?");
    $sql->bind_param("i", $id);
    $sql->execute();
    $result = $sql->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// function teacher upload riddle
function teacher_upload_riddle($id_user, $title, $description, $file) {
    global $conn;
    $sql = $conn->prepare("INSERT INTO riddles (teacher, title, description, file) VALUES (?, ?, ?, ?)");
    $sql->bind_param("isss", $id_user, $title, $description, $file);
    if ($sql->execute() === TRUE) {
        return true;
    } else {
        return false;
    }
}

// function check riddle submission
function check_riddle_submission($answer, $riddle_id) {
    global $conn;
    $sql = $conn->prepare("SELECT * FROM riddles WHERE file = ? AND id = ?");
    $sql->bind_param("si", $answer, $riddle_id);
    $sql->execute();
    $result = $sql->get_result();
    if ($result->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}
?>
