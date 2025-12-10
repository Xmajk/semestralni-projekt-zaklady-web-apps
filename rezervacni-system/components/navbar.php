<?php
require_once __DIR__."/utils/links.php";

// KONFIGURACE: Zde změňte doménu, pokud bude potřeba
$external_url = "https://www.kacubo.cz";

$isLogged = false;
$isAdmin = false;

if (isset($_COOKIE['is_logged'])) {
    $isLogged = filter_var($_COOKIE['is_logged'], FILTER_VALIDATE_BOOLEAN);

    $isLogged = $isLogged && !str_ends_with($_SERVER["REQUEST_URI"],"login.php");

    if ($isLogged) {
        if (isset($_COOKIE['is_admin'])) {
            $isAdmin = filter_var($_COOKIE['is_admin'], FILTER_VALIDATE_BOOLEAN);
        }
    }
}

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
            <div class="nazev"><a class="navbar-brand" href="<?= $external_url ?>/index.htm">Kacubó Kenrikai 渇望剣理会</a> </div>
            <div class="kk"><a class="navbar-brand" href="<?= $external_url ?>/index.htm">Kacubó  渇剣</a></div>
        </div>
        <div class="collapse navbar-collapse" id="myNavbar">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="<?= $external_url ?>/jakzacit.htm">Jak začít</a></li>
                <li class="dropdown"> <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">O nás <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= $external_url ?>/zakladniinfo.htm">Základní informace</a></li>
                        <li><a href="<?= $external_url ?>/historiekacubo.htm">Historie Kacubó</a></li>
                        <li><a href="<?= $external_url ?>/clenove.htm">Členové a učitelé</a></li>
                        <li><a href="<?= $external_url ?>/poharsacuki.htm">Pohár Sacuki</a></li>
                    </ul>
                </li>
                <li class="dropdown"> <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">O Kendó <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= $external_url ?>/cojekendo.htm">Co je Kendó</a></li>
                        <li><a href="<?= $external_url ?>/historiekendo.htm">Historie Kendó</a></li>
                        <li><a href="<?= $external_url ?>/zakladni.htm">Základní koncepty</a></li>
                        <li><a href="<?= $external_url ?>/vybaveni.htm">Vybavení na Kendó</a></li>
                        <li><a href="<?= $external_url ?>/struktura.htm">Struktura tréninku</a></li>
                        <li><a href="<?= $external_url ?>/technika.htm">Technika Kendó</a></li>
                        <li><a href="<?= $external_url ?>/siai.htm">Šiai - zápas v Kendó</a></li>
                        <li><a href="<?= $external_url ?>/stupne.htm">Stupně v Kendó</a></li>
                        <li><a href="<?= $external_url ?>/etiketa.htm">Etiketa</a></li>
                        <li><a href="<?= $external_url ?>/kata.htm">Kendó Kata</a></li>
                        <li><a href="<?= $external_url ?>/slovnicek.htm">Slovníček</a></li>
                        <li><a href="<?= $external_url ?>/literatura.htm">Literatura a odkazy</a></li>
                    </ul>
                </li>
                <li><a href="<?= $external_url ?>/foto.htm">Foto</a></li>
                <li><a href="<?= $external_url ?>/kontakt.htm">Kontakt</a></li>

                <?php if ($isLogged): ?>

                    <li class="dropdown"> <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">Události<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <?php if ($isAdmin): ?>
                                <li><a href="<?= createLink("/admin/index.php")?>">Admin</a></li>
                            <?php endif; ?>
                            <li><a href="<?= $external_url ?>/zakladniinfo.htm">Účet</a></li>

                            <li><a href="<?= createLink("/index.php")?>">Události</a></li>
                            <li><a id="logoutBtn" class="tw-cursor-pointer" href="<?= createLink("/logout.php") ?>">Odhlásit se</a></li>
                        </ul>
                    </li>

                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>