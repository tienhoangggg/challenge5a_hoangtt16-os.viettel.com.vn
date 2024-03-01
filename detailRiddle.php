<?php
require_once("verifyJWT.php");

$curUser = verifyJWT();
if ($curUser === null) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $riddles = get_riddle_detail($_GET['id']);
    echo "<a href='riddle.php'>Back</a><br>";
    echo "<h3> Title: " . htmlspecialchars($riddles['title']) . "</h3>";
    echo "<p> teacher: " . htmlspecialchars($riddles['poster']) . "</p>";
    echo "<p> Description: " . htmlspecialchars($riddles['description']) . "</p>";
    //form to upload submission
    if ($curUser['role'] === "student") {
        echo "<form action='detailRiddle.php' method='post' enctype='multipart/form-data'>";
        echo "<input type='hidden' name='id' value='" . $_GET['id'] . "'>";
        echo "<input type='hidden' name='teacher' placeholder='teacher' value='" . htmlspecialchars($riddles['teacher']) . "'>";
        echo "<input type='hidden' name='title' placeholder='title' value='" . htmlspecialchars($riddles['title']) . "'>";
        echo "<input type='text' name='fileName' placeholder='fileName'>";
        echo "<button type='submit' name='submit'>Send</button>";
        echo "</form>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($curUser['role'] !== "student") {
        echo json_encode(array("status" => "error", "message" => "You are not a student"));
        exit();
    }
    $fileName = $_POST['fileName'] . ".txt";
    $teacher = $_POST['teacher'];
    $title = $_POST['title'];
    $answer = hash('sha256', $teacher . $title . $fileName) . ".txt";
    if(check_riddle_submission($answer, $_POST['id'])) {
        echo "Your answer is correct<br>";
        //show the file
        $file = STORAGE_DIR . $answer;
        if (file_exists($file)) {
            echo "<a href='" . $file . "'>Download</a>";
        }
        else {
            echo "File not found";
        }
        exit();
    }
    else {
        echo "Your answer is incorrect";
        header("Location: detailRiddle.php?id=" . $_POST['id']);
        exit();
    }
}
?>