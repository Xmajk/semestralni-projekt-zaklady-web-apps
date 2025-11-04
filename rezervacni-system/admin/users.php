<?php?>

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
<body id="admin-index-body">
<header>
    <?php include "../components/navbar.php"; ?>
</header>
<div id="page-content">

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
            <tr>
                <td class="clip">USB-C Hub Pro 7-in-1</td>
                <td class="muted">Doplňky</td>
                <td class="num">1 290 Kč</td>
                <td><span class="badge badge--ok">ANO</span></td>
                <td><a href="">upravit</a></td>
                <td><a href="">vymazat</a></td>
            </tr>
            <tr>
                <td class="clip">Mechanická klávesnice 75%</td>
                <td class="muted">Periferie</td>
                <td class="num">2 990 Kč</td>
                <td><span class="badge">Na cestě</span></td>
            </tr>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>

