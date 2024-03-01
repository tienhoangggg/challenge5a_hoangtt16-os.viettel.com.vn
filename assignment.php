<?php
require_once("verifyJWT.php");
require_once("config.php");
require_once("database.php");


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $curUser = verifyJWT();
    if ($curUser === null) {
        header("Location: login.php");
        exit();
    }
    //get all assignments
    $assignments = get_all_assignments();
    //button to go back to index
    echo "<a href='index.php'>Back</a><br>";
    foreach ($assignments as $assignment) {
        echo "<h3> Title: " . htmlspecialchars($assignment['title']) . "</h3>";
        echo "<p> Teacher: " . htmlspecialchars($assignment['poster']) . "</p>";
        echo "<a href='detailAssignment.php?id=" . $assignment['id'] . "'>Detail</a><br>";
        echo "------------------------<br>";
    }
    //form to upload assignment (only teacher can upload assignment)
    if ($curUser['role'] === "teacher") {
        echo "<form action='assignment.php' method='post' enctype='multipart/form-data'>";
        echo "<input type='text' name='title' placeholder='Title'>";
        echo "<input type='text' name='description' placeholder='Description'>";
        echo "<input type='file' name='file'>";
        echo "<button type='submit' name='submit'>Upload</button>";
        echo "</form>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $curUser = verifyJWT();
    if ($curUser === null) {
        echo json_encode(array("status" => "error", "message" => "Invalid token"));
        exit();
    }
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
    $allowed = array('pdf', 'doc', 'docx', 'txt', 'zip', 'rar', '7z');
    if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 10000000) {
                $fileNameNew = hash('sha256', $curUser['id'] . rand(0,999999999) . rand(0,999999999) . rand(0,999999999)) . "." . $fileActualExt;
                $fileDestination = STORAGE_DIR . $fileNameNew;
                move_uploaded_file($fileTmpName, $fileDestination);
                if(teacher_upload_assignment($curUser['id'], $title, $description, $fileNameNew)) {
                    header("Location: assignment.php");
                    exit();
                } else {
                    echo json_encode(array("status" => "error", "message" => "There was an error uploading your file"));
                }
            } else {
                echo json_encode(array("status" => "error", "message" => "Your file is too big"));
            }
        } else {
            echo json_encode(array("status" => "error", "message" => "There was an error uploading your file"));
        }
    } else {
        echo json_encode(array("status" => "error", "message" => "You cannot upload files of this type"));
    }
}
?>