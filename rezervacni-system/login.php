<?php
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once __DIR__ . "/components/dbconnector.php";
    require_once __DIR__ . "/components/utils/crypto.php";
    $conn = connect();

    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($username)) {
        $error = "uživatelské jméno je povinné";
    }

    if (empty($password)) {
        $error = "heslo je povinné";
    }

    $sql = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        $error = "Uživatel nebyl nalezen";
    } else {
        $user = $result->fetch_assoc(); // ✅ načtení dat z řádku jako pole

        if ($user['password'] != hashSHA256($password)) {
            $error = "Heslo není správné";
        } else {
            setcookie("is_logged", true);
            setcookie("username", $username);
            setcookie("user_id", $user['id']);
            setcookie("is_admin", $user['is_admin']);
            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="public/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="icon" type="image/x-icon" href="https://www.kacubo.cz/favicon.ico">
<title>Přihlášení – Kacubó Kenrikai</title>
<style>
@media(max-width:480px){
  .navbar__menu{display:none;}
  .login-container{padding:20px;}
}

#switch-wrapper{
  height: 20px;
  margin: 10px 0 10px 0;
  width: 100%;
  display: flex;
  flex-direction: row;
}
#switch-wrapper > -.switch{
  flex: 1;
}
#switch-wrapper > -#remember-label{
  flex: 9;
}


</style>
<link rel="stylesheet" href="/rezervacni-system/public/styles/forms.css">
<link rel="stylesheet" href="/rezervacni-system/public/styles/toogleswitch.css">
</head>
<body id="login-body">
<header>
    <?php include "components/navbar.php"; ?>
</header>

<main class="login-page">
  <div class="login-container">
    <form id="login-form" method="post" action="">
        <h2>Přihlášení</h2>
        <?php if ($error): ?>
            <p class="error"><?=htmlspecialchars($error)?></p>
        <?php endif; ?>


      <div>
        <label for="username" class="">Uživatelské jméno</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div>
        <label for="password" class="">Heslo</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div>
        <div class="chckbox-wrapper">
            <input type="checkbox">
            <label>zobrazit heslo</label>
        </div>
          <div class="chckbox-wrapper">
              <input type="checkbox">
              <label>pamatovat si mě</label>
          </div>
      </div>
      <button type="submit">Přihlásit se</button>
    </form>
  </div>
</main>
</body>
</html>
