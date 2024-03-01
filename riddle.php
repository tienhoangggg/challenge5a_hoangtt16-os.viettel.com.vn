<?php
require_once ("verifyJWT.php");

$curUser = verifyJWT();
if ($curUser === null) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $riddles = get_all_riddles();
    echo "<a href='index.php'>Back</a><br>";
    foreach ($riddles as $riddle) {
        echo "<h3> Title: " . htmlspecialchars($riddle['title']) . "</h3>";
        echo "<p> teacher: " . htmlspecialchars($riddle['poster']) . "</p>";
        echo "<a href='detailRiddle.php?id=" . $riddle['id'] . "'>Detail</a><br>";
        echo "------------------------<br>";
    }
    if ($curUser['role'] === "teacher") {
        echo "<form action='riddle.php' method='post' enctype='multipart/form-data'>";
        echo "<input type='text' name='title' placeholder='Title'>";
        echo "<input type='text' name='description' placeholder='Description'>";
        echo "<input type='file' name='file'>";
        echo "<button type='submit' name='submit'>Upload</button>";
        echo "</form>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($curUser['role'] !== "teacher") {
        echo json_encode(array("status" => "error", "message" => "You are not a teacher"));
        exit();
    }
    $title = $_POST['title'];
    $description = $_POST['description'];
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];
    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));
    $allowed = array('txt');
    if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 10000000) {
                $fileNameNew = hash('sha256', $curUser['id'] . $title . $fileName) . "." . $fileActualExt;
                $fileDestination = STORAGE_DIR . $fileNameNew;
                move_uploaded_file($fileTmpName, $fileDestination);
                if(teacher_upload_riddle($curUser['id'], $title, $description, $fileNameNew)) {
                    header("Location: riddle.php");
                    exit();
                }
            } else {
                echo json_encode(array("status" => "error", "message" => "Your file is too big"));
                exit();
            }
        } else {
            echo json_encode(array("status" => "error", "message" => "There was an error uploading your file"));
            exit();
        }
    } else {
        echo json_encode(array("status" => "error", "message" => "You cannot upload files of this type"));
        exit();
    }
}
?>