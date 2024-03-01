<?php
require_once("verifyJWT.php");
$curUser = verifyJWT();
if ($curUser === null) {
    header("Location: login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['isDelete'] === "true") {
        if (delete_message($curUser['id'], $_POST['id'])) {
            header("Location: chatBox.php?id=" . $_POST['id_user']);
        } else {
            echo "Error deleting message";
        }
    }
}
?>