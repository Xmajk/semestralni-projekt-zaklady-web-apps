<?php
require_once __DIR__ . "/../components/check_auth.php";
check_auth_admin();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/../components/head.php" ?>
    <link rel="stylesheet" href="/rezervacni-system/public/styles/forms.css">
    <link rel="stylesheet" href="/rezervacni-system/public/styles/toogleswitch.css">
</head>
<body id="admin-index-body">
<header>
    <?php include "../components/navbar.php"; ?>
</header>
<div id="page-content">
    <div id="links-div">
        <a class="button-link" href="users.php">uživatelé</a>
        <a class="button-link" href="events.php">události</a>
    </div>
</div>
</body>
</html>
