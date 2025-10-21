<?php
require_once __DIR__ . "/components/objects/Event.php";
use components\objects\Event;
$error = "";

$events = array();

$tmp = new Event();
$tmp->registered = false;
$tmp->locked = false;
$tmp->name = "Event1";
$tmp->description = "";

array_push($events, $tmp);

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/rezervacni-system/public/styles/style.css" rel="stylesheet" type="text/css">
    <link href="/rezervacni-system/public/styles/index.css" rel="stylesheet" type="text/css">
    <link rel="icon" type="image/x-icon" href="https://www.kacubo.cz/favicon.ico">
    <title>Rezervace – Kacubó Kenrikai</title>
    <style>

    </style>
    <link rel="stylesheet" href="/rezervacni-system/public/styles/forms.css">
    <link rel="stylesheet" href="/rezervacni-system/public/styles/toogleswitch.css">
</head>
<body id="home-body">
<header>
    <?php include "components/navbar.php"; ?>
</header>
<div class="events-container">

    <?php foreach ($events as $event): ?>
        <div class="event-card available">
            <div class="event-image" style="background-image:url('/rezervacni-system/public/imgs/default-event-bg.png');"></div>
            <div class="event-content">
                <div class="event-title"><?= htmlspecialchars($event->name) ?></div>
                <div class="event-date">Datum konání: 25. listopadu 2025</div>
                <div class="event-date">Registrace do: 25. listopadu 2025</div>
                <div class="event-location">Praha, Karlín</div>
                <div class="event-description">
                    Přijďte poznat naše projekty, tým a zúčastněte se workshopů.
                </div>
            </div>
            <div class="event-actions">
                <button onclick="window.location.href='/info'">Více informací</button>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="event-card joined">
        <div class="event-image" style="background-image:url('https://www.kacubo.cz/favicon.ico');"></div>
        <div class="event-content">
            <div class="event-title">Hackathon Kacubo 2025</div>
            <div class="event-date">12.–14. prosince 2025</div>
            <div class="event-location">Brno</div>
            <div class="event-description">
                48hodinový maraton programování zaměřený na webové technologie.
            </div>
        </div>
        <div class="event-actions">
            <button onclick="window.location.href='/info'">Zobrazit detail</button>
        </div>
    </div>

    <div class="event-card locked">
        <div class="event-image" style="background-image:url('https://www.kacubo.cz/header/143.jpg');"></div>
        <div class="event-content">
            <div class="event-title">Interní školení</div>
            <div class="event-date">20. října 2025</div>
            <div class="event-location">Online (Teams)</div>
            <div class="event-description">
                Uzavřený kurz pro zaměstnance o nových postupech v DevOps.
            </div>
        </div>
        <div class="event-actions">
            <button disabled>Nelze se přihlásit</button>
        </div>
    </div>

</div>
</body>
</html>
