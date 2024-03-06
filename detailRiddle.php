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
        $jwt = createJWT(array("id" => $answer, "time" => time()));
        setcookie('jwt_' . str_replace('.', '_', $answer), $jwt, time() + 3600, "/download.php", "", true, true);
        echo "<a href='riddle.php'>Back</a><br>";
        echo "Your answer is correct<br>";
        echo "<a href='". "download.php?id=" . $answer . "'>Download</a><br>";
        exit();
    }
    else {
        echo "Your answer is incorrect";
        header("Location: detailRiddle.php?id=" . $_POST['id']);
        exit();
    }
}
?>