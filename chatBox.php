<?php
# users can chat with each other
require_once("verifyJWT.php");
$curUser = verifyJWT();
if ($curUser === null) {
    header("Location: login.php");
    exit();
}
// if method GET is used, show chat history
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (get_role($_GET['id']) === null) {
        echo "User not found";
        exit();
    }
    $chat = get_chat($curUser['id'], $_GET['id']);
    if ($chat === null) {
        echo "Error getting chat";
        exit();
    }
    //button to go back to infoUser
    echo "<a href='infoUser.php?id=" . $_GET['id'] . "'>Back</a><br>";
    foreach ($chat as $msg) {
        echo $msg['username'] . ": ";
        //button to edit message, click button to show form to edit message
        if ($msg['sender'] === $curUser['id']) {
            echo "<form action='editMessage.php' method='post'>";
            echo "<input type='hidden' name='isEdit' value='true'>";
            echo "<input type='hidden' name='id' value='" . $msg['id'] . "'>";
            echo "<input type='hidden' name='id_user' value='" . $_GET['id'] . "'>";
            echo "<input type='text' name='message' value='" . htmlspecialchars($msg['message']) . "'>";
            echo "<input type='submit' value='Edit'>";
            echo "</form>";
        }
        else
        {
            echo htmlspecialchars($msg['message']) . "<br>";
        }
        //button to delete message
        if ($msg['sender'] === $curUser['id']) {
            echo "<form action='deleteMessage.php' method='post'>";
            echo "<input type='hidden' name='isDelete' value='true'>";
            echo "<input type='hidden' name='id' value='" . $msg['id'] . "'>";
            echo "<input type='hidden' name='id_user' value='" . $_GET['id'] . "'>";
            echo "<input type='submit' value='Delete'>";
            echo "</form>";
        }
        echo "----------------------------<br>";
    }
    echo "<form action='chatBox.php' method='post'>";
    echo "<input type='hidden' name='id' value='" . $_GET['id'] . "'>";
    echo "<input type='text' name='message' placeholder='Type your message'>";
    echo "<input type='submit' value='Send'>";
    echo "</form>";
}

// if method POST is used, send message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (get_role($_POST['id']) === null) {
        echo "User not found";
        exit();
    }
    if ( $_POST['message'] === "") {
        echo "Message cannot be empty";
        exit();
    }
    if (send_message($curUser['id'], $_POST['id'],  $_POST['message'])) {
        header("Location: chatBox.php?id=" . $_POST['id']);
        exit();
    } else {
        echo "Error sending message";
    }
}
?>