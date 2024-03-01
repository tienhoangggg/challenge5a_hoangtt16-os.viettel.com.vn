<?php
require_once("verifyJWT.php");
$curUser = verifyJWT();
if ($curUser === null) {
    header("Location: login.php");
    exit();
}
//button to login
echo "<a href='login.php'>Logout</a><br>------------------------<br>";
//button to assignment
echo "<a href='assignment.php'>Assignment</a><br>------------------------<br>";
//button to riddle
echo "<a href='riddle.php'>Riddle</a><br>------------------------<br>";
$users = get_all_users();
foreach ($users as $user) {
    echo "Role: " . htmlspecialchars($user['role']) . "<br>";
    echo "Username: " . htmlspecialchars($user['username']) . "<br>";
    //button to GET infoUser
    echo "<a href='infoUser.php?id=" . $user['id'] . "'>Info</a><br>";
    echo "---<br>";
}
?>