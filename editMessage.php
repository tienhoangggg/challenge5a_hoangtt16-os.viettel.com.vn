<?php
require_once("verifyJWT.php");
$curUser = verifyJWT();
if ($curUser === null) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['isEdit'] === "true") {
        if ($_POST['message'] === "") {
            echo "Message cannot be empty";
            exit();
        }
        if (edit_message($curUser['id'], $_POST['id'], $_POST['message'])) {
            header("Location: chatBox.php?id=" . $_POST['id_user']);
        } else {
            echo "Error editing message";
        }
    }
}
?>