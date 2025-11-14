<?php
use components\objects\User;
require_once __DIR__ . "/../components/objects/User.php";
require_once __DIR__ . "/../components/utils/crypto.php";
require_once __DIR__ . "/../components/utils/links.php";
require_once __DIR__ . "/../components/check_auth.php";

check_auth_admin();

$error = NULL;

// 1. Načtení ID uživatele z URL
$user_id = $_GET["id"] ?? null;

if (!$user_id) {
    // Pokud chybí ID uživatele, přesměruj na seznam uživatelů
    header("Location: " . createLink("/admin/users.php"));
    exit;
}

// 2. Načtení uživatele z DB
$user = User::getUserById($user_id);
if (!$user) {
    // Pokud uživatel s daným ID neexistuje
    header("Location: " . createLink("/admin/users.php?error=user_not_found"));
    exit;
}


// 3. Zpracování požadavků
if($_SERVER["REQUEST_METHOD"] == "GET" && (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']))){
    // Zpracování smazání (pokud by bylo voláno odsud)
    $id = (int)$_GET['id'];
    User::deleteById($id);
    header("Location: " .createLink("/admin/users.php"));
    exit;
}
else if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Zpracování AKTUALIZACE uživatele

    // Získání a ošetření dat z POST
    $new_firstname  = trim($_POST["firstname"] ?? "");
    $new_lastname   = trim($_POST["lastname"] ?? "");
    $new_email      = trim($_POST["email"] ?? "");
    $new_bdate      = trim($_POST["bdate"] ?? "");
    $new_password   = trim($_POST["password"] ?? ""); // Nové heslo, může být prázdné
    $new_is_admin   = isset($_POST["is_admin"]) ? 1 : 0; // Zjištění, zda je admin checkbox zaškrtnut

    // Základní server-side validace
    if (empty($new_firstname) || empty($new_lastname) || empty($new_email) || empty($new_bdate)) {
        $error = "Pole Jméno, Příjmení, Email a Datum narození jsou povinná.";
    } else {
        // Všechna povinná pole jsou vyplněna, pokračujeme v aktualizaci

        // Aktualizuj vlastnosti objektu $user
        $user->firstname  = $new_firstname;
        $user->lastname   = $new_lastname;
        $user->email      = $new_email;
        $user->bdate      = $new_bdate;
        $user->is_admin   = $new_is_admin;

        // Aktualizace hesla POUZE pokud bylo zadáno nové
        if (!empty($new_password)) {
            $user->password = hashSHA256($new_password);
        }
        // Pokud je $new_password prázdné, $user->password zůstane původní (načtené z DB)

        // Zavolání metody update (která musí být definována v User.php)
        if ($user->update()) {
            // Úspěšná aktualizace, přesměruj se zprávou o úspěchu
            header("Location: " . createLink("/admin/user.php?id=" . $user->id . "&success=1"));
            exit;
        } else {
            $error = "Chyba při ukládání změn do databáze.";
        }
    }
}

// Pokud kód došel sem, buď se jedná o první načtení stránky (GET),
// nebo došlo k chybě při POSTu. V obou případech se zobrazí formulář
// s aktuálními daty (buď čerstvě načtenými z DB, nebo upravenými z POSTu, které selhalo).

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/../components/head.php" ?>
    <link rel="stylesheet" href="<?= createStylesLink("/forms.css") ?>">
    <link rel="stylesheet" href="/rezervacni-system/public/styles/forms.css">
    <link rel="stylesheet" href="/rezervacni-system/public/styles/toogleswitch.css">
    <script src="<?= createScriptLink("/validation/users.js") ?>"></script>
</head>
<body id="admin-user-body">
<header>
    <?php include "../components/navbar.php"; ?>
</header>
<div id="page-content">
    <form id="add-user-form" autocomplete="off" method="post">
        <span id="form-error" class="error">
            <?php
            // Zobrazení obecné chyby, pokud je k dispozici (např. v budoucnu: chyba DB)
            if (isset($errors['general'])):
                echo htmlspecialchars($errors['general']);
            endif;
            ?>
        </span>

        <div id="username-wrapper" class="form-wrapper">
            <label for="form-username">Uživatelské jméno</label>
            <input id="form-username" type="text" name="username" placeholder="Uživatelské jméno" required autocomplete="off" aria-describedby="error-username"
                   value="<?= htmlspecialchars($user->username) ?>" disabled>
        </div>

        <div id="email-wrapper" class="form-wrapper">
            <label for="form-email">Email</label>
            <input id="form-email" type="email" name="email" placeholder="E-mail" required aria-describedby="error-email"
                   value="<?= htmlspecialchars($user->email ?? '') ?>">
            <span id="error-email" class="validation-error <?= isset($errors['email']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['email'] ?? '') ?>
            </span>
        </div>

        <div id="names-wrapper" class="form-wrapper">
            <div id="firstname-wrapper">
                <label for="form-firstname">Jméno</label>
                <input id="form-firstname" type="text" name="firstname" placeholder="Jméno" required aria-describedby="error-firstname"
                       value="<?= htmlspecialchars($user->firstname ?? '') ?>">
                <span id="error-firstname" class="validation-error <?= isset($errors['firstname']) ? 'active' : '' ?>">
                    <?= htmlspecialchars($errors['firstname'] ?? '') ?>
                </span>
            </div>
            <div id="lastname-wrapper">
                <label for="form-lastname">Příjmení</label>
                <input id="form-lastname" type="text" name="lastname" placeholder="Příjmení" required aria-describedby="error-lastname"
                       value="<?= htmlspecialchars($user->lastname ?? '') ?>">
                <span id="error-lastname" class="validation-error <?= isset($errors['lastname']) ? 'active' : '' ?>">
                    <?= htmlspecialchars($errors['lastname'] ?? '') ?>
                </span>
            </div>
        </div>

        <div id="bdate-wrapper">
            <label for="form-bdate">Datum narození</label>
            <input id="form-bdate" type="date" name="bdate" required aria-describedby="error-bdate"
                   value="<?= htmlspecialchars($user->bdate ?? '') ?>">
            <span id="error-bdate" class="validation-error <?= isset($errors['bdate']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['bdate'] ?? '') ?>
            </span>
        </div>

        <div id="password-wrapper" class="form-wrapper">
            <label for="form-password">Heslo</label>
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
</div>
</body>
</html>