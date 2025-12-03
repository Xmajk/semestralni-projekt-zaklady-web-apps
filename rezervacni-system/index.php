<?php
use components\objects\Event;
use components\objects\Registration;

const EVENTS_PER_PAGE = 1;


require_once __DIR__ . "/components/objects/Event.php";
require_once __DIR__ . "/components/objects/Registration.php";
require_once __DIR__ . "/components/check_auth.php";
require_once __DIR__ . "/components/utils/date_time.php";
require_once __DIR__ . "/components/utils/links.php";
require_once __DIR__ . "/components/utils/numbers.php";

function eventIsLocked(Event $event):bool{
    $event_deadline = convertStringToDateTime($event->registration_deadline);
    $now = new DateTime();
    return ($event_deadline->getTimestamp()-$now->getTimestamp())<=0;
}

function userIsRegistered(int $user_id, int $event_id):bool{
    $result = false;
    try{
        $result = Registration::existsByUserIdAndEventId($user_id, $event_id);
    }catch (Exception $exception){
        redirect_to(create_error_link("Chyba databáze"));
    }
    return $result;
}

check_auth_user();
$error = "";
$now = new DateTime();
$user_id = isset($_COOKIE["user_id"]) ? (int)$_COOKIE["user_id"] : 0;
$numberOfEvents = Event::countEvents();
$numberOfPages = ceil($numberOfEvents / EVENTS_PER_PAGE);
$currentPage=$_GET["page"]??"1";
if(!is_numeric($currentPage) && $currentPage != "max"){
    redirect_to(create_error_link("Chybné číslo stránky"));
}else if($currentPage=="max"){
    $currentPage=$numberOfPages;
}
$currentPage=intval($currentPage);
if($currentPage<1){
    redirect_to(create_error_link("Chybné číslo stránky"));
}
if($currentPage>$numberOfPages){
    $currentPage=intval($numberOfPages);
}
$pagination = [];
if($currentPage!=1){
    $pagination[]=$currentPage-1;
}
$pagination[]=$currentPage;
if($currentPage!=$numberOfPages){
    $pagination[]=$currentPage+1;
}
$events = Event::getPage(EVENTS_PER_PAGE, $currentPage);
$userEvents = [];
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
<body id="home-body">
<header>
    <?php include "components/navbar.php"; ?>
</header>
<div class="events-container">

    <?php foreach ($events as $event): ?>
        <div class="event-card <?php
            if(userIsRegistered($user_id, $event->id)){
                echo "joined";
            }else if(eventIsLocked($event)){
                echo "locked";
            } else{
                echo "available";
            };
        ?>">
            <img height="300" class="event-image" src="<?= create_small_image_link($event->image_filename??"default.jpg") ?>">
            <!--<div class="event-image" style="background-image:url('/rezervacni-system/public/imgs/default-event-bg.png');"></div>-->
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
                <a class="link-as-btn rounded" href="<?= createLink("/event.php?".http_build_query(["id"=>$event->id])) ?>">Více informací</a>
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
<div class="pagination-wrap">
    <ul class="pagination">
        <li>
            <a href="<?= createLink("/index.php?".http_build_query(["page"=>1])) ?>">
                <span aria-hidden="true">&laquo;</span>
                <span class="visuallyhidden">previous set of pages</span>
            </a>
        </li>
        <li>
            <a href=""><span class="visuallyhidden">page </span>1</a>
        </li>
        <li>
            <a href="" aria-current="page">
                <span class="visuallyhidden">page </span>2
            </a>
        </li>
        <li>
            <a href=""> <span class="visuallyhidden">page </span>3 </a>
        </li>
        <li>
            <a href=""> <span class="visuallyhidden">page </span>4 </a>
        </li>
        <li>
            <a href="<?= createLink("/index.php?".http_build_query(["page"=>"max"])) ?>">
        <span class="visuallyhidden">next set of pages</span
        ><span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</div>
</body>
</html>
