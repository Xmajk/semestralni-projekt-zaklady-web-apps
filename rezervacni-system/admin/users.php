<?php
use components\objects\User;
require_once __DIR__ . "/../components/objects/User.php";
require_once __DIR__ . "/../components/utils/crypto.php";
require_once __DIR__ . "/../components/utils/links.php";
require_once __DIR__ . "/../components/check_auth.php";
check_auth_admin();

if($_SERVER["REQUEST_METHOD"] == "POST"){
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

$error = NULL;

$users = User::getAllOrdered();


?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/../components/head.php" ?>
    <link rel="stylesheet" href="/rezervacni-system/public/styles/forms.css">
    <link rel="stylesheet" href="/rezervacni-system/public/styles/toogleswitch.css">
</head>
<body id="admin-users-body">
<header>
    <?php include "../components/navbar.php"; ?>
</header>
<div id="page-content">

    <button class="expand-btn" onclick="toggleForm()">Přidat uživatele</button>

    <form id="add-user-form" autocomplete="off" method="post" action="">
        <span id="form-error"></span>
        <div id="username-wrapper" class="form-wrapper">
            <label for="form-username">Uživatelské jméno</label>
            <input id="form-username" type="text" name="username" placeholder="Uživatelské jméno" required autocomplete="off">
        </div>
        <div id="email-wrapper" class="form-wrapper">
            <label for="form-email">Email</label>
            <input id="form-email" type="email" name="email" placeholder="E-mail" required>
        </div>
        <div id="names-wrapper" class="form-wrapper">
            <div id="firstname-wrapper">
                <label for="form-firstname">Jméno</label>
                <input id="form-firstname" type="text" name="firstname" placeholder="Jméno" required>
            </div>
            <div id="lastname-wrapper">
                <label for="form-lastname">Příjmení</label>
                <input id="form-lastname" type="text" name="lastname" placeholder="Příjmení" required>
            </div>
        </div>
        <div id="bdate-wrapper">
            <label for="form-bdate">Datum narození</label>
            <input id="form-bdate" type="date" name="bdate" required>
        </div>
        <div id="password-wrapper" class="form-wrapper">
            <label for="form-password">Heslo</label>
            <input id="form-password" type="password" name="password" placeholder="Heslo" required>
        </div>
        <div id="isadmin-wrapper" class="form-wrapper">
            <label></label>

        </div>
        <button type="submit">Uložit</button>
    </form>

    <div>
        <label for="filter">Filtrování</label>
        <input name="filter" class="filter-username" type="text" placeholder="Uživatelské jméno">
    </div>

    <div class="table-wrap" data-density="comfy">
        <table class="table">
            <thead>
            <tr>
                <th class="sortable" aria-sort="none">Uživatelské jméno</th>
                <th>Jméno</th>
                <th>Datum narození</th>
                <th>Admin</th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>

                <tr>
                    <td mark="username" class="clip"><?= htmlspecialchars($user->username) ?></td>
                    <td class="muted"><?= htmlspecialchars($user->firstname) ?></td>
                    <td class="muted"><?= htmlspecialchars($user->bdate) ?></td>

                    <td><span class="badge badge--ok">
                        <?php if ($user->is_admin == 1):
                            echo "ano";
                        else:
                            echo "ne";
                        endif;
                        ?>
                        </span></td>
                    <td><a href="<?= createLink("/admin/user.php?id=".$user->id) ?>">upravit</a></td>
                    <td><a href="<?= createLink("/admin/user.php?action=delete&id=".$user->id)?>">vymazat</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="<?= createScriptLink("/users.js") ?>"></script>

</body>
</html>

