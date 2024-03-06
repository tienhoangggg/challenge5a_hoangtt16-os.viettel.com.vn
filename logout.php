<?php
//delete all cookies
$cookies = array_keys($_COOKIE);
foreach($cookies as $cookie) {
    setcookie($cookie, "", time() - 3600);
}

header("Location: login.php");
exit();
?>