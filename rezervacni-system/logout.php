<?php
    setcookie("is_logged", "", time() - 3600);
    setcookie("is_admin", "", time() - 3600);
    setcookie("username", "", time() - 3600);
    setcookie("user_id", "", time() - 3600);

    unset($_COOKIE['is_logged']);
    unset($_COOKIE['is_admin']);
    unset($_COOKIE['username']);
    unset($_COOKIE['user_id']);
    header("Location: login.php");
?>
