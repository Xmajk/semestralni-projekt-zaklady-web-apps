<?php
require_once __DIR__ . "/components/utils/links.php"; // Pro funkci createLink

// Výchozí chybová hláška
$errorMessage = "Došlo k neočekávané chybě. Zkuste to prosím znovu později.";

// Zkontrolujeme, zda byla předána konkrétní hláška přes GET parametr
if (isset($_GET['message']) && !empty(trim($_GET['message']))) {
    // Bezpečnost: Sanitizujeme hlášku pro zobrazení, abychom zabránili XSS
    $errorMessage = htmlspecialchars($_GET['message']);
}

// Odkaz na hlavní stránku
$homeLink = createLink("/index.php");
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/components/head.php"; ?>
    <title>Chyba - Rezervační systém</title>
    <style>
        /* Styly pro chybovou stránku, aby zapadla do designu */
        body#error-page-body {
            background-color: var(--primary-background-color, #f7f7f7);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100vw;
            overflow-x: hidden;
        }

        .error-container {
            /* Inspirováno .login-container ze style.css */
            text-align: center;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin: auto; /* Centrování na stránce */
            max-width: 700px;
            width: 90%;
            margin-top: 120px; /* Odsazení od navigace */
        }

        .error-container h1 {
            color: var(--primary-kacubo, #a20d0d);
            font-size: 2.5rem;
            margin-bottom: 20px;
            overflow: auto; /* Z main style.css */
        }

        .error-container .error-message {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-container .back-link {
            /* Použijeme styl podobný tlačítkům */
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--primary-kacubo, #a20d0d);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .error-container .back-link:hover {
            background-color: var(--primary-kacubo-hover, #8c0b0b);
            text-decoration: none;
        }
    </style>
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