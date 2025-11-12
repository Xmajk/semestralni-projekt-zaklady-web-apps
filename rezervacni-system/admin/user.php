<?php
use components\objects\User;
require_once __DIR__ . "/../components/objects/User.php";
require_once __DIR__ . "/../components/utils/crypto.php";
require_once __DIR__ . "/../components/utils/links.php";
require_once __DIR__ . "/../components/check_auth.php";

session_start();
check_auth_admin();

// Načteme chyby a data formuláře ze session (pokud existují)
$errors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
// Ihned je ze session smažeme (flash data)
unset($_SESSION['form_errors'], $_SESSION['form_data']);

// Zpracování DELETE požadavku (mazání)
if($_SERVER["REQUEST_METHOD"] == "GET" && (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']))){
    $id = (int)$_GET['id'];
    User::deleteById($id);
    header("Location: " .createLink("/admin/users.php"));
    exit;
}
// Zpracování POST požadavku (aktualizace)
else if($_SERVER["REQUEST_METHOD"] == "POST"){
    $user_id = $_POST['user_id'] ?? null;
    $user = User::getUserById($user_id);

    if (!$user) {
        // Chyba, uživatel neexistuje, přesměrovat pryč
        header("Location: " . createLink("/admin/users.php"));
        exit;
    }

    // 1. Uložit data z POSTu do $formData pro případné znovuvyplnění
    $formData = [
            'firstname' => $_POST["firstname"] ?? '',
            'lastname' => $_POST["lastname"] ?? '',
            'email' => $_POST["email"] ?? '',
            'bdate' => $_POST["bdate"] ?? '',
            'is_admin' => isset($_POST['is_admin']) ? 1 : 0,
        // username se neposílá, je disabled
        // heslo se řeší zvlášť
    ];

    // 2. Naplnit objekt User daty z formuláře
    $user->firstname = $formData["firstname"];
    $user->lastname  = $formData["lastname"];
    $user->email     = $formData["email"];
    $user->bdate     = $formData["bdate"];
    $user->is_admin  = $formData["is_admin"];

    // 3. Validace objektu (true = update)
    $isValid = true;
    $errors = [];
    $passwordUpdate = false;

    // 4. Validace hesla (pouze pokud bylo zadáno nové)
    $password = $_POST["password"] ?? '';
    if (!empty(trim($password))) {
        if (strlen($password) < 6) {
            $errors['password'] = "Heslo musí mít alespoň 6 znaků.";
            $isValid = false;
        } else {
            // Heslo je platné, připravit k aktualizaci
            $user->password = hashSHA256($password);
            $passwordUpdate = true;
        }
    }

    // 5. Zkontrolovat, zda je vše v pořádku
    if ($isValid) {
        // Žádné chyby, aktualizovat DB
        $user->update(); // Aktualizuje info
        if ($passwordUpdate) {
            $user->updatePassword(); // Aktualizuje heslo
        }
    } else {
        // Chyby, uložit do session pro další požadavek
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $formData;
    }

    // VŽDY přesměrovat po POSTu (PRG pattern)
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Zobrazení stránky (GET požadavek)
$user_id = $_GET["id"] ?? null;
$user = User::getUserById($user_id);

if (!$user) {
    // Uživatel nenalezen, přesměrovat
    header("Location: " . createLink("/admin/users.php"));
    exit;
}

// Pokud existují flash data (z neúspěšného POSTu), přepíšeme jimi data z DB
// To zajistí, že se ve formuláři objeví data, která uživatel odeslal (a která selhala)
if (!empty($formData)) {
    $user->firstname = $formData['firstname'] ?? $user->firstname;
    $user->lastname  = $formData['lastname'] ?? $user->lastname;
    $user->email     = $formData['email'] ?? $user->email;
    $user->bdate     = $formData['bdate'] ?? $user->bdate;
    $user->is_admin  = $formData['is_admin'] ?? $user->is_admin;
}

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
    <a href="users.php"><- zpět</a>

    <form id="add-user-form" autocomplete="off" method="post" action="">
        <!-- Skryté pole pro ID uživatele, klíčové pro POST -->
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user->id) ?>">

        <?php if (!empty($errors)): ?>
            <div class="form-error-summary">Prosím opravte chyby ve formuláři.</div>
        <?php endif; ?>

        <div id="username-wrapper" class="form-wrapper">
            <label for="username">Uživatelské jméno</label>
            <input type="text" name="username" placeholder="Uživatelské jméno" disabled value="<?= htmlspecialchars($user->username) ?>" required autocomplete="off">
        </div>

        <div id="email-wrapper" class="form-wrapper">
            <label for="email">Email</label>
            <input type="email" name="email" placeholder="E-mail" required
                   value="<?= htmlspecialchars($user->email ?? '') ?>"
                   class="<?= isset($errors['email']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['email'])): ?>
                <span class="form-input-error"><?= htmlspecialchars($errors['email']) ?></span>
            <?php endif; ?>
        </div>

        <div id="names-wrapper" class="form-wrapper">
            <div id="firstname-wrapper">
                <label for="firstname">Jméno</label>
                <input type="text" name="firstname" placeholder="Jméno" required
                       value="<?= htmlspecialchars($user->firstname ?? '') ?>"
                       class="<?= isset($errors['firstname']) ? 'input-error' : '' ?>">
                <?php if (isset($errors['firstname'])): ?>
                    <span class="form-input-error"><?= htmlspecialchars($errors['firstname']) ?></span>
                <?php endif; ?>
            </div>
            <div id="lastname-wrapper">
                <label for="lastname">Příjmení</label>
                <input type="text" name="lastname" placeholder="Příjmení" required
                       value="<?= htmlspecialchars($user->lastname ?? '') ?>"
                       class="<?= isset($errors['lastname']) ? 'input-error' : '' ?>">
                <?php if (isset($errors['lastname'])): ?>
                    <span class="form-input-error"><?= htmlspecialchars($errors['lastname']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div id="bdate-wrapper">
            <label for="bdate">Datum narození</label>
            <input type="date" name="bdate" required
                   value="<?= htmlspecialchars($user->bdate ?? '') ?>"
                   class="<?= isset($errors['bdate']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['bdate'])): ?>
                <span class="form-input-error"><?= htmlspecialchars($errors['bdate']) ?></span>
            <?php endif; ?>
        </div>

        <div id="password-wrapper" class="form-wrapper">
            <label for="password">Nové heslo</label>
            <input type="password" name="password" placeholder="Nové heslo (nechte prázdné pro zachování)"
                   class="<?= isset($errors['password']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['password'])): ?>
                <span class="form-input-error"><?= htmlspecialchars($errors['password']) ?></span>
            <?php endif; ?>
        </div>

        <div id="isadmin-wrapper" class="form-wrapper">
            <label for="form-is-admin">Je admin?</label>
            <label class="switch">
                <input id="form-is-admin" type="checkbox" name="is_admin" value="1"
                        <?php echo ($user->is_admin == 1) ? 'checked' : ''; ?>>
                <span class="slider round"></span>
            </label>
        </div>

        <button type="submit">Uložit změny</button>
    </form>
</div>
</body>
</html>