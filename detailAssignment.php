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
    if ($assignment === null) {
        echo "Assignment not found";
        exit();
    }
    $jwt = createJWT(array("id" => $assignment['file'], "time" => time()));
    setcookie('jwt_' . str_replace('.', '_', $assignment['file']), $jwt, time() + 3600, '/download.php', '', true, true);
    $body = "<a href='assignment.php'>Back</a><br>";
    $body = $body . "<h3> Title: " . htmlspecialchars($assignment['title']) . "</h3>";
    $body = $body . "<p> Teacher: " . htmlspecialchars($assignment['poster']) . "</p>";
    $body = $body . "<p> Description: " . htmlspecialchars($assignment['description']) . "</p>";
    $body = $body . "<a href='". "download.php?id=" . $assignment['file'] . "'>Download</a><br>------------------------<br>";
    //show all submissions
    $submissions = get_all_submissions($curUser['id'], $id);
    foreach ($submissions as $submission) {
        $body = $body . "<p> Student: " . htmlspecialchars($submission['poster']) . "</p>";
        //create a cookie with jwt to verify that user has permission to download the file
        $jwt = createJWT(array("id" => $submission['file'], "time" => time()));
        setcookie('jwt_' . str_replace('.', '_', $submission['file']), $jwt, time() + 3600, "/download.php", "", true, true);
        $body = $body . "<a href='". "download.php?id=" . $submission['file'] . "'>Download</a>";
        $body = $body . "<p> Created at: " . $submission['created_at'] . "</p>------------------------<br>";
    }
    if ($curUser['role'] === "student") {
        $body = $body . "<form action='detailAssignment.php' method='post' enctype='multipart/form-data'>";
        $body = $body . "<input type='file' name='file'>";
        $body = $body . "<input type='hidden' name='assignment_id' value='" . $id . "'>";
        $body = $body . "<button type='submit' name='submit'>Upload</button>";
        $body = $body . "</form>";
    }
    echo $body;
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