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
            'username' => $_POST["username"] ?? '',
            'firstname' => $_POST["firstname"] ?? '',
            'lastname' => $_POST["lastname"] ?? '',
            'email' => $_POST["email"] ?? '',
            'bdate' => $_POST["bdate"] ?? '',
            'is_admin' => isset($_POST['is_admin']) ? 1 : 0
    ];

    $newUser = new User();
    $newUser->username   = $formData["username"];
    $newUser->firstname  = $formData["firstname"];
    $newUser->lastname   = $formData["lastname"];
    $newUser->email      = $formData["email"];
    $newUser->bdate      = $formData["bdate"];
    $newUser->is_admin   = $formData["is_admin"];

    $valid_user = true;
    $errors = [];
    $password = $_POST["password"] ?? '';
    if (empty(trim($password))) {
        $errors['password'] = "Heslo je povinné.";
    } else if (strlen($password) < 6) {
        $errors['password'] = "Heslo musí mít alespoň 6 znaků.";
    }
    $newUser->password = hashSHA256($password);

    if ($valid_user && empty($errors['password'])) {
        $newUser->insert();
    } else {
        // Chyby, uložit do session pro další požadavek
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
    <link rel="stylesheet" href="/rezervacni-system/public/styles/forms.css">
    <link rel="stylesheet" href="/rezervacni-system/public/styles/toogleswitch.css">
</head>
<body id="admin-users-body">
<header>
    <?php include "../components/navbar.php"; ?>
</header>
<div id="page-content">

    <button class="expand-btn" onclick="toggleForm()">Přidat uživatele</button>

    <form id="add-user-form" autocomplete="off" method="post" action="" style="<?php echo !empty($errors) ? 'display:block;' : ''; ?>">

        <?php if (!empty($errors)): ?>
            <div class="form-error-summary">Prosím opravte chyby ve formuláři.</div>
        <?php endif; ?>

        <div id="username-wrapper" class="form-wrapper">
            <label for="form-username">Uživatelské jméno</label>
            <input id="form-username" type="text" name="username" placeholder="Uživatelské jméno" required autocomplete="off"
                   value="<?= htmlspecialchars($formData['username'] ?? '') ?>"
                   class="<?= isset($errors['username']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['username'])): ?>
                <span class="form-input-error"><?= htmlspecialchars($errors['username']) ?></span>
            <?php endif; ?>
        </div>

        <div id="email-wrapper" class="form-wrapper">
            <label for="form-email">Email</label>
            <input id="form-email" type="email" name="email" placeholder="E-mail" required
                   value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                   class="<?= isset($errors['email']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['email'])): ?>
                <span class="form-input-error"><?= htmlspecialchars($errors['email']) ?></span>
            <?php endif; ?>
        </div>

        <div id="names-wrapper" class="form-wrapper">
            <div id="firstname-wrapper">
                <label for="form-firstname">Jméno</label>
                <input id="form-firstname" type="text" name="firstname" placeholder="Jméno" required
                       value="<?= htmlspecialchars($formData['firstname'] ?? '') ?>"
                       class="<?= isset($errors['firstname']) ? 'input-error' : '' ?>">
                <?php if (isset($errors['firstname'])): ?>
                    <span class="form-input-error"><?= htmlspecialchars($errors['firstname']) ?></span>
                <?php endif; ?>
            </div>

            <div id="lastname-wrapper">
                <label for="form-lastname">Příjmení</label>
                <input id="form-lastname" type="text" name="lastname" placeholder="Příjmení" required
                       value="<?= htmlspecialchars($formData['lastname'] ?? '') ?>"
                       class="<?= isset($errors['lastname']) ? 'input-error' : '' ?>">
                <?php if (isset($errors['lastname'])): ?>
                    <span class="form-input-error"><?= htmlspecialchars($errors['lastname']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div id="bdate-wrapper">
            <label for="form-bdate">Datum narození</label>
            <input id="form-bdate" type="date" name="bdate" required
                   value="<?= htmlspecialchars($formData['bdate'] ?? '') ?>"
                   class="<?= isset($errors['bdate']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['bdate'])): ?>
                <span class="form-input-error"><?= htmlspecialchars($errors['bdate']) ?></span>
            <?php endif; ?>
        </div>

        <div id="password-wrapper" class="form-wrapper">
            <label for="form-password">Heslo</label>
            <input id="form-password" type="password" name="password" placeholder="Heslo (min. 6 znaků)" required
                   class="<?= isset($errors['password']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['password'])): ?>
                <span class="form-input-error"><?= htmlspecialchars($errors['password']) ?></span>
            <?php endif; ?>
        </div>

        <div id="isadmin-wrapper" class="form-wrapper">
            <label for="form-is-admin">Je admin?</label>
            <label class="switch">
                <input id="form-is-admin" type="checkbox" name="is_admin" value="1"
                        <?php echo (!empty($formData['is_admin']) && $formData['is_admin'] == 1) ? 'checked' : ''; ?>>
                <span class="slider round"></span>
            </label>
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