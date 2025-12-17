<?php

use components\objects\User;

require_once __DIR__ . "/components/objects/User.php";
require_once __DIR__ . "/components/utils/crypto.php";
require_once __DIR__ . "/components/utils/links.php";

session_start();
$error = $_SESSION["errors"]["login"]??null;
$form_data = $_SESSION["form_data"]??[];
$_SESSION["errors"]=[];
$username = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $form_data["username"] = $username;
    $_SESSION["form_data"] = $form_data;
    $password = trim($_POST["password"] ?? "");

    if (empty($username) || empty($password)) {
        $_SESSION["errors"] = [
                "login" => "Chybné uživatelské jméno nebo heslo"
        ];
        redirect_to(createLink("/login.php"));
    } else {
        try {
            $user = User::getUserByUsername($username);
            if ($user && password_verify($password, $user->password)) {
                $isAdminValue = $user->is_admin ? "1" : "0";

                setcookie("is_logged", "true", time() + (86400 * 30), "/");
                setcookie("username", $user->username, time() + (86400 * 30), "/");
                setcookie("user_id", (string)$user->id, time() + (86400 * 30), "/");
                setcookie("is_admin", $isAdminValue, time() + (86400 * 30), "/");

                redirect_to(createLink("/index.php"));
            } else {
                $_SESSION["errors"] = [
                        "login" => "Chybné uživatelské jméno nebo heslo"
                ];
                redirect_to(createLink("/login.php"));
            }
        } catch (Exception $e) {
            redirectToDatabaseError();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <link href="public/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="icon" type="image/x-icon" href="https://www.kacubo.cz/favicon.ico">
    <title>Rezervace</title>
    <link rel="stylesheet" href="/rezervacni-system/public/styles/forms.css">
    <link rel="stylesheet" href="/rezervacni-system/public/styles/toogleswitch.css">
</head>
<body id="login-body">
<header>
    <?php include "components/navbar.php"; ?>
</header>

<main class="login-page">
  <div class="login-container">
    <form id="login-form" method="post">
        <h2>Přihlášení</h2>
        <?php if ($error): ?>
            <p class="error"><?=htmlspecialchars($error)?></p>
        <?php endif; ?>


      <div>
        <label for="username" class="">Uživatelské jméno</label>
        <input type="text" id="username" name="username"
               <?php if (isset($error)): ?>
                    value="<?=htmlspecialchars($_SESSION["form_data"]["username"]??"")?>"
               <?php endif; ?>
               required>
      </div>
      <div>
        <label for="password" class="">Heslo</label>
        <input type="password" id="password" name="password" required>
      </div>
        <!--
      <div>
        <div class="chckbox-wrapper">
            <input type="checkbox">
            <label>zobrazit heslo</label>
        </div>
      </div>-->
      <button type="submit">Přihlásit se</button>
    </form>
  </div>
</main>
</body>
</html>
