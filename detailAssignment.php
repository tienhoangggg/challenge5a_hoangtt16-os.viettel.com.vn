<?php
require_once("verifyJWT.php");
require_once("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $curUser = verifyJWT();
    if ($curUser === null) {
        header("Location: login.php");
        exit();
    }
    $id = $_GET['id'];
    $assignment = get_assignment_detail($id);
    echo "<a href='assignment.php'>Back</a><br>";
    if ($assignment === null) {
        echo "Assignment not found";
        exit();
    }
    echo "<h3> Title: " . $assignment['title'] . "</h3>";
    echo "<p> Teacher: " . $assignment['poster'] . "</p>";
    echo "<p> Description: " . $assignment['description'] . "</p>";
    echo "<a href='". STORAGE_DIR . $assignment['file'] . "'>Download</a><br>------------------------<br>";
    //show all submissions
    $submissions = get_all_submissions($curUser['id'], $id);
    foreach ($submissions as $submission) {
        echo "<p> Student: " . $submission['poster'] . "</p>";
        echo "<a href='". STORAGE_DIR . $submission['file'] . "'>Download</a>";
        echo "<p> Created at: " . $submission['created_at'] . "</p>------------------------<br>";
    }
    if ($curUser['role'] === "student") {
        echo "<form action='detailAssignment.php' method='post' enctype='multipart/form-data'>";
        echo "<input type='file' name='file'>";
        echo "<input type='hidden' name='assignment_id' value='" . $id . "'>";
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
    if ($curUser['role'] !== "student") {
        echo json_encode(array("status" => "error", "message" => "You are not a student"));
        exit();
    }
    $assignmentId = $_POST['assignment_id'];
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
                if(student_upload_assignment($curUser['id'], $assignmentId, $fileNameNew)) {
                    header("Location: detailAssignment.php?id=" . $assignmentId);
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