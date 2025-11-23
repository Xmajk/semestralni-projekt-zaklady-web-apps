<?php
use components\objects\User;
require_once __DIR__ . "/../components/objects/User.php";
require_once __DIR__ . "/../components/utils/crypto.php";
require_once __DIR__ . "/../components/utils/links.php";
require_once __DIR__ . "/../components/check_auth.php";

session_start();
check_auth_admin();

$errors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $formData = [
            'username' => trim($_POST["username"] ?? ''),
            'firstname' => trim($_POST["firstname"] ?? ''),
            'lastname' => trim($_POST["lastname"] ?? ''),
            'email' => trim($_POST["email"] ?? ''),
            'bdate' => trim($_POST["bdate"] ?? ''),
            'is_admin' => isset($_POST['is_admin']) ? 1 : 0
    ];

    $errors = [];
    $password = $_POST["password"] ?? '';
    $MIN_PASSWORD_LENGTH = 8;
    $MIN_USERNAME_LENGTH = 3;

    // SERVER-SIDE VALIDACE

    // 1. Uživatelské jméno
    if (empty($formData['username'])) {
        $errors['username'] = "Uživatelské jméno je povinné.";
    } else if (strlen($formData['username']) < $MIN_USERNAME_LENGTH) {
        $errors['username'] = "Uživatelské jméno musí mít alespoň {$MIN_USERNAME_LENGTH} znaky.";
    }

    // 2. Jméno
    if (empty($formData['firstname'])) {
        $errors['firstname'] = "Jméno je povinné.";
    }

    // 3. Příjmení
    if (empty($formData['lastname'])) {
        $errors['lastname'] = "Příjmení je povinné.";
    }

    // 4. E-mail
    if (empty($formData['email'])) {
        $errors['email'] = "E-mail je povinný.";
    } else if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Zadejte prosím platnou e-mailovou adresu.";
    }
    // Dále by bylo dobré zkontrolovat unikátnost e-mailu/uživ. jména v databázi, ale to vyžaduje kód v User.php

    // 5. Datum narození
    if (empty($formData['bdate'])) {
        $errors['bdate'] = "Datum narození je povinné.";
    }

    // 6. Heslo
    if (empty($password)) {
        $errors['password'] = "Heslo je povinné.";
    } else if (strlen($password) < $MIN_PASSWORD_LENGTH) {
        $errors['password'] = "Heslo musí mít alespoň {$MIN_PASSWORD_LENGTH} znaků.";
    }

    // Pokud nejsou žádné chyby, ulož uživatele
    if (empty($errors)) {
        $newUser = new User();
        $newUser->username   = $formData["username"];
        $newUser->firstname  = $formData["firstname"];
        $newUser->lastname   = $formData["lastname"];
        $newUser->email      = $formData["email"];
        $newUser->bdate      = $formData["bdate"];
        $newUser->is_admin   = $formData["is_admin"];
        $newUser->password   = hashSHA256($password);

        try{
            $newUser->insert();
        }catch (Exception $e){
            //CHyba při ukládání usera
            echo $e->getMessage();
        }

    } else {
        // Chyby, uložit do session pro znovunačtení
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $formData;
    }

    // VŽDY přesměrovat po POSTu (PRG pattern)
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
$users = User::getAllOrdered();

?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/../components/head.php" ?>
    <script src="<?= createScriptLink("/validation/users.js") ?>"></script>
</head>
<body id="admin-users-body">
<header>
    <?php include "../components/navbar.php"; ?>
</header>
<div id="page-content">

    <button id="expand-user-form" class="expand-btn">Přidat uživatele</button>

    <form id="add-user-form" class="novisible" autocomplete="off" method="post">
        <span id="form-error" class="error">
            <?php
            if (isset($errors['general'])):
                echo htmlspecialchars($errors['general']);
            endif;
            ?>
        </span>

        <div id="username-wrapper" class="form-wrapper">
            <label for="form-username">Uživatelské jméno<span class="required"></span></label>
            <input id="form-username" type="text" name="username" placeholder="Uživatelské jméno" required autocomplete="off" aria-describedby="error-username"
                   value="<?= htmlspecialchars($formData['username'] ?? '') ?>">
            <span id="error-username" class="validation-error <?= isset($errors['username']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['username'] ?? '') ?>
            </span>
        </div>

        <div id="email-wrapper" class="form-wrapper">
            <label for="form-email">Email<span class="required"></span></label>
            <input id="form-email" type="email" name="email" placeholder="E-mail" required aria-describedby="error-email"
                   value="<?= htmlspecialchars($formData['email'] ?? '') ?>">
            <span id="error-email" class="validation-error <?= isset($errors['email']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['email'] ?? '') ?>
            </span>
        </div>

        <div id="names-wrapper" class="form-wrapper">
            <div id="firstname-wrapper">
                <label for="form-firstname">Jméno<span class="required"></span></label>
                <input id="form-firstname" type="text" name="firstname" placeholder="Jméno" required aria-describedby="error-firstname"
                       value="<?= htmlspecialchars($formData['firstname'] ?? '') ?>">
                <span id="error-firstname" class="validation-error <?= isset($errors['firstname']) ? 'active' : '' ?>">
                    <?= htmlspecialchars($errors['firstname'] ?? '') ?>
                </span>
            </div>
            <div id="lastname-wrapper">
                <label for="form-lastname">Příjmení<span class="required"></span></label>
                <input id="form-lastname" type="text" name="lastname" placeholder="Příjmení" required aria-describedby="error-lastname"
                       value="<?= htmlspecialchars($formData['lastname'] ?? '') ?>">
                <span id="error-lastname" class="validation-error <?= isset($errors['lastname']) ? 'active' : '' ?>">
                    <?= htmlspecialchars($errors['lastname'] ?? '') ?>
                </span>
            </div>
        </div>

        <div id="bdate-wrapper">
            <label for="form-bdate">Datum narození<span class="required"></span></label>
            <input id="form-bdate" type="date" name="bdate" required aria-describedby="error-bdate"
                   value="<?= htmlspecialchars($formData['bdate'] ?? '') ?>">
            <span id="error-bdate" class="validation-error <?= isset($errors['bdate']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['bdate'] ?? '') ?>
            </span>
        </div>

        <div id="password-wrapper" class="form-wrapper">
            <label for="form-password">Heslo<span class="required"></span></label>
            <input id="form-password" type="password" name="password" placeholder="Heslo" required aria-describedby="error-password">
            <span id="error-password" class="validation-error <?= isset($errors['password']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['password'] ?? '') ?>
            </span>
        </div>

        <div id="isadmin-wrapper" class="form-wrapper">
            <label for="form-isadmin">Admin práva</label>
            <div id="switch-wrapper" style="height: 34px;">
                <label class="switch" style="width: 60px;">
                    <input type="checkbox" id="form-isadmin" name="is_admin" value="1"
                            <?= ($formData['is_admin'] ?? 0) ? 'checked' : '' ?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>

        <button type="submit">Uložit</button>
    </form>

    <div class="filter-username-wrap">
        <label for="filter">Filtrování</label>
        <input id="filter" name="filter" class="filter-username" type="text" placeholder="Uživatelské jméno">
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
                    <td class="clip username-col"><?= htmlspecialchars($user->username) ?></td>
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