<?php
require_once __DIR__ . "/../components/check_auth.php";
require_once __DIR__ . "/../components/breadcrumb.php";
check_auth_admin();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/../components/head.php" ?>
</head>
<body id="admin-index-body">
<header>
    <?php include "../components/navbar.php"; ?>
</header>
<div id="page-content">
    <?= generateBreadcrumbs(["Home","Admin"]) ?>
    <div id="links-div">
        <a class="button-link" href="users.php">uživatelé</a>
        <a class="button-link" href="events.php">události</a>
    </div>
</div>
</body>
</html>
