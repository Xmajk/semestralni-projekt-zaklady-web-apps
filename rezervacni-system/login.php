<?php
// login.php – základní ukázka přihlášení

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    // Vlastní ověřování
    if ($username === "admin" && $password === "heslo123") {
        header("Location: index.php");
        exit;
    } else {
        $error = "Neplatné uživatelské jméno nebo heslo.";
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Přihlášení – Kacubó Kenrikai</title>
<style>

body {
  width: 100%;
  height: 100%
  font-family: Arial, sans-serif;
  background-color:#f7f7f7;
  color:#333;
  line-height:1.5;
  background: url('https://www.kacubo.cz/header/143.jpg') center/cover no-repeat;;
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  background-attachment: fixed; 
  /*height: 100vh;*/
}

/* LOGIN */
.login-page {
  display:flex;
  justify-content:center;
  align-items:center;
  padding:40px 20px;
  min-height:calc(100vh - 120px);
  /*background:url('https://www.kacubo.cz/header/143.jpg') center/cover no-repeat; /* můžeš dát vlastní */
}
.login-container {
  background-color:rgba(255,255,255,0.95);
  padding:30px 40px;
  border-radius:8px;
  box-shadow:0 4px 12px rgba(0,0,0,0.1);
  width:100%;
  max-width:400px;
}
.login-container h2 {
  margin-bottom:20px;
  color:#a20d0d;
  text-align:center;
}
.login-container label {
  display:block;
  margin-bottom:5px;
  font-weight:bold;
}
.login-container input {
  width:100%;
  padding:10px;
  margin-bottom:15px;
  border:1px solid #ccc;
  border-radius:4px;
}
.login-container input:focus {
  border-color:#a20d0d;
  outline:none;
}
.login-container button {
  width:100%;
  padding:12px;
  background-color:#a20d0d;
  color:white;
  border:none;
  border-radius:4px;
  font-size:1rem;
  cursor:pointer;
}
.login-container button:hover {background-color:#800000;}
.login-footer-text {
  margin-top:15px;
  text-align:center;
  font-size:0.9rem;
}
.login-footer-text a {
  color:#a20d0d;
  text-decoration:none;
}
.login-footer-text a:hover{text-decoration:underline;}
.error {
  color:red;
  margin-bottom:10px;
  text-align:center;
}

/* Footer */
/*
footer {
  background-color:#a20d0d;
  color:#ccc;
  text-align:center;
  padding:15px 0;
  font-size:0.9rem;
}
*/
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
<body>
<header>
    <?php include "components/navbar.php"; ?>
</header>

<main class="login-page">
  <div class="login-container">
    <h2>Přihlášení</h2>
    <?php if ($error): ?>
      <p class="error"><?=htmlspecialchars($error)?></p>
    <?php endif; ?>
    <form method="post" action="">
      <div>
        <label for="username" class="">Uživatelské jméno</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div>
        <label for="password" class="">Heslo</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div id="switch-wrapper">
        <label class="switch">
          <input type="checkbox" name="remember">
          <span class="slider"></span>
        </label>
        <label id="remember-label" for="remember">pamatovasi mě</label>
      </div>
      <button type="submit">Přihlásit se</button>
    </form>
  </div>
</main>
</body>
</html>
