<?php

use components\objects\Event;

require_once __DIR__ . "/components/objects/Event.php";
require_once __DIR__ . "/components/utils/links.php";

$event_id =null;
if($_SERVER["REQUEST_METHOD"] != "GET" && $_SERVER["REQUEST_METHOD"] != "POST"){
    redirect_to(create_error_link("404 chyba"));
}

$event_id = $_GET["id"]??null;
if(!isset($event_id)){
    redirect_to(create_error_link("Chybí parametr id"));
}
$event = Event::getById($event_id);
if(!isset($event)){
    redirect_to(create_error_link("Event nenalezen"));
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    //TODO registrace
    redirect_to(createLink("/event.php?".http_build_query(["id" => $event_id])));
}


?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="public/styles/style.css" rel="stylesheet" type="text/css">
    <link href="public/styles/index.css" rel="stylesheet" type="text/css">
    <link rel="icon" type="image/x-icon" href="https://www.kacubo.cz/favicon.ico">
    <title>Rezervace – Kacubó Kenrikai</title>
    <link rel="stylesheet" href="/rezervacni-system/public/styles/forms.css">
    <link rel="stylesheet" href="/rezervacni-system/public/styles/toogleswitch.css">
</head>
<body id="event-body">
<header>
    <?php include "components/navbar.php"; ?>
</header>
<div id="event-container">
    <div class="event-div">
        <h2>Event1sadjkanksjandkjasnkasjdnaskjdnaskjkjasndkasdkjajndas1111</h2>
        <a class="kacubo-ref" href="<?= createLink("index.php") ?>"><- zpět na akce</a>
        <div class="dates-wrapper">
            <div class="date-div">
                <p>Začátek: 5.5.2025</p>
            </div>
            <div class="date-div">
                <p>Konec: 5.5.2025</p>
            </div>
        </div>
        <div class="date-div">
            <p>Registrace do: 5.5.2025</p>
        </div>
        <div class="price-div">
            <p>Cena: 5000kč</p>
        </div>
        <div class="line-div"></div>
        <div class="description-wrapper">
            asdjasdnkjans
        </div>

        <div class="map-wrapper">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2558.699459907536!2d14.388536376940978!3d50.11063211177282!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x470beaa724e9edd3%3A0x6d5f8d9f9f18c82b!2sKacub%C3%B3%20Kenrikai%20Kend%C3%B3!5e0!3m2!1scs!2scz!4v1761119616680!5m2!1scs!2scz" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <div>
            <p>Obsazeno 25/50</p>
        </div>

        <button class="event-button">registrovat se</button>
    </div>
</div>
</body>
</html>

