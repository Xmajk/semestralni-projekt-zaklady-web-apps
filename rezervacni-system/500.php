<?php
require_once __DIR__ . "/components/utils/links.php";

$errorMessage = "Došlo k neočekávané chybě. Zkuste to prosím znovu později.";

if (isset($_GET['message']) && !empty(trim($_GET['message']))) {
    $errorMessage = htmlspecialchars($_GET['message']);
}

$homeLink = createLink("/index.php");
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/components/head.php"; ?>
    <link href="<?= createStylesLink("/responsivity.css") ?>" rel="stylesheet" type="text/css">
    <link href="<?= createStylesLink("/errorPage.css") ?>" rel="stylesheet" type="text/css">
</head>
<body id="error-page-body">
<header>
    <?php include __DIR__ . "/components/navbar.php"; ?>
</header>

<main class="error-container">
    <h1>Něco se pokazilo</h1>
    <p class="error-message">
        <?php echo $errorMessage; ?>
    </p>
    <a href="<?php echo $homeLink; ?>" class="back-link">Zpět na hlavní stránku</a>
</main>
</body>
</html>