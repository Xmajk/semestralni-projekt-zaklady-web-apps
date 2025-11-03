<?php
    setcookie("islogged", false);
    setcookie("username", "");
    setcookie("user_id", "");
    header("Location: login.php");
?>
