<?php
use components\objects\User;
require_once __DIR__ . "/../components/objects/User.php";
require_once __DIR__ . "/../components/utils/crypto.php";
require_once __DIR__ . "/../components/utils/links.php";
require_once __DIR__ . "/../components/check_auth.php";

check_auth_admin();

if($_SERVER["REQUEST_METHOD"] == "GET" && (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']))){
    $id = (int)$_GET['id'];
    User::deleteById($id);
    header("Location: " .createLink("/admin/users.php"));
    exit;
}
else if($_SERVER["REQUEST_METHOD"] == "PUT"){
    /*Create new user*/
    $newUser = new User();
    $newUser->username   = $_POST["username"];
    $newUser->firstname  = $_POST["firstname"];
    $newUser->lastname   = $_POST["lastname"];
    $newUser->email      = $_POST["email"];
    $newUser->bdate      = $_POST["bdate"];
    $newUser->password   = hashSHA256($_POST["password"]);
    $newUser->is_admin   = 0;

    $newUser->insert();

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$user_id = $_GET["id"];
$user = User::getUserById($user_id);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/../components/head.php" ?>
    <link rel="stylesheet" href="/rezervacni-system/public/styles/forms.css">
    <link rel="stylesheet" href="/rezervacni-system/public/styles/toogleswitch.css">
</head>
<body id="admin-user-body">
<header>
    <?php include "../components/navbar.php"; ?>
</header>
<div id="page-content">
    <form id="add-user-form" autocomplete="off" method="put" action="">
        <div id="username-wrapper" class="form-wrapper">
            <label for="username">Uživatelské jméno</label>
            <input type="text" name="username" placeholder="Uživatelské jméno" disabled value="<?= $user->username ?>" required autocomplete="off">
        </div>
        <div id="email-wrapper" class="form-wrapper">
            <label for="email">Email</label>
            <input type="email" name="email" placeholder="E-mail" value="<?= $user->email?>" required>
        </div>
        <div id="names-wrapper" class="form-wrapper">
            <div id="firstname-wrapper">
                <label for="firstname">Jméno</label>
                <input type="text" name="firstname" placeholder="Jméno" required value="<?= $user->firstname?>">
            </div>
            <div id="lastname-wrapper">
                <label for="lastname">Příjmení</label>
                <input type="text" name="lastname" placeholder="Příjmení" required value="<?= $user->lastname?>">
            </div>
        </div>
        <div id="bdate-wrapper">
            <label for="bdate">Datum narození</label>
            <input type="date" name="bdate" required value="<?= $user->bdate?>">
        </div>
        <div id="password-wrapper" class="form-wrapper">
            <label for="password">Heslo</label>
            <input type="password" name="password" placeholder="Heslo" required>
        </div>
        <button type="submit">Uložit</button>
    </form>
</div>
</body>
</html>

