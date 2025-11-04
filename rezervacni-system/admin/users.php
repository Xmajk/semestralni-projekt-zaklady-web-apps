<?php

if($_SERVER["REQUEST_METHOD"] == "POST"){

}

$error = NULL;


?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../public/styles/style.css" rel="stylesheet" type="text/css">
    <link href="../public/styles/index.css" rel="stylesheet" type="text/css">
    <link rel="icon" type="image/x-icon" href="https://www.kacubo.cz/favicon.ico">
    <title>Rezervace – Kacubó Kenrikai</title>
    <link rel="stylesheet" href="/rezervacni-system/public/styles/forms.css">
    <link rel="stylesheet" href="/rezervacni-system/public/styles/toogleswitch.css">
</head>
<body id="admin-users-body">
<header>
    <?php include "../components/navbar.php"; ?>
</header>
<div id="page-content">

    <!-- 🔘 Tlačítko na rozbalení -->
    <button class="expand-btn" onclick="toggleForm()">Přidat uživatele</button>

    <!-- 🧾 Skrytý formulář -->
    <form id="add-user-form" autocomplete="off" method="post" action="">
        <div id="username-wrapper" class="form-wrapper">
            <label for="username">Uživatelské jméno</label>
            <input type="text" name="username" placeholder="Uživatelské jméno" required autocomplete="off">
        </div>
        <div id="email-wrapper" class="form-wrapper">
            <label for="email">Email</label>
            <input type="email" name="email" placeholder="E-mail" required>
        </div>
        <div id="names-wrapper" class="form-wrapper">
            <div id="firstname-wrapper">
                <label for="firstname">Jméno</label>
                <input type="text" name="firstname" placeholder="Jméno" required>
            </div>
            <div id="lastname-wrapper">
                <label for="lastname">Příjmení</label>
                <input type="text" name="lastname" placeholder="Příjmení" required>
            </div>
        </div>
        <div id="bdate-wrapper">
            <label for="bdate">Datum narození</label>
            <input type="date" name="bdate" required>
        </div>
        <div id="password-wrapper" class="form-wrapper">
            <label for="password">Heslo</label>
            <input type="password" name="password" placeholder="Heslo" required>
        </div>
        <button type="submit">Uložit</button>
    </form>

    <div class="table-wrap" data-density="comfy">
        <table class="table">
            <thead>
            <tr>
                <th class="sortable" aria-sort="none">Uživatelské jméno</th>
                <th>Jméno</th>
                <th>Příjmení</th>
                <th>Admin</th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            for ($i = 1; $i <= 500; $i++) {
                echo '
    <tr>
        <td class="clip">USB-C Hub Pro 7-in-1</td>
        <td class="muted">Doplňky</td>
        <td class="num">1 290 Kč</td>
        <td><span class="badge badge--ok">ANO</span></td>
        <td><a href="">upravit</a></td>
        <td><a href="">vymazat</a></td>
    </tr>';
            }
            ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    function toggleForm() {
        const form = document.getElementById('add-user-form');
        form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    }
</script>

</body>
</html>

