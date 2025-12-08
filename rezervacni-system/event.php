<?php
use components\objects\Event;
use components\objects\Registration;

require_once __DIR__ . "/components/objects/Event.php";
require_once __DIR__ . "/components/objects/Registration.php";
require_once __DIR__ . "/components/utils/links.php";
require_once __DIR__ . "/components/utils/date_time.php";
require_once __DIR__ . "/components/check_auth.php";
require_once __DIR__ . "/components/breadcrumb.php";

check_auth_user();
$current_user_id = $_COOKIE['user_id'] ?? null;
$event_id = $_GET["id"] ?? null;
if (!$event_id || !is_numeric($event_id)) {
    redirect_to(create_error_link("Neplatné ID události."));
}

$event = Event::getById((int)$event_id);
if (!$event) {
    redirect_to(create_error_link("Událost nebyla nalezena."));
}

if($_SERVER["REQUEST_METHOD"] === "POST") {
    $registerUserID = $_POST["registerUserID"];
    $registerEventID = $_POST["registerEventID"];
    $registerDatetime = new DateTime("now", new DateTimeZone("Europe/Prague"));
    $registrationExists = $_POST["registrationExists"] === "true" ? true : false;
    if($registerUserID === null || $registerEventID === null || $registerDatetime === null) {
        redirect_to(create_error_link("500 chyba"));
    }
    $event = Event::getById(intval($registerEventID));
    if($event==null) {
        redirect_to(create_error_link("Event nebyl nalezen"));
    }
    if($registerDatetime->getTimestamp() > convertStringToDateTime($event->registration_deadline)->getTimestamp()) {
        redirect_to(create_error_link("Registrace skončila"));
    }

    $registration = new Registration();
    $registration->id_event = $registerEventID;
    $registration->id_user = $registerUserID;
    $registration->registration_datetime = $registerDatetime->format("Y-m-d H:i:s");
    if($registrationExists === true) {
        if(Registration::deleteRegistration($registration)==false){
            redirect_to(create_error_link("Nastala chyba."));
        };
    }else{
        if(Registration::createRegistration($registration)==false){
            redirect_to(create_error_link("Nastala chyba."));
        };
    }
    redirect_to(createLink("/event.php?".http_build_query(["id" => $event_id])));
}

$location = $event->location;
$capacity = intval($event->capacity);
$price = $event->price;
$occupancy = Registration::numberOfRegistrationsByEventId($event_id);
$is_registered = Registration::existsByUserIdAndEventId($current_user_id, $event_id);
if($is_registered === null) {
    redirect_to(create_error_link("Chyba databáze"));
}

$start_date = new DateTime($event->start_datetime, new DateTimeZone("Europe/Prague"));
$deadline = new DateTime($event->registration_deadline, new DateTimeZone("Europe/Prague"));
$now = new DateTime();

$is_past_deadline = $now > $deadline;
$is_full = $occupancy >= $capacity;
$event_started = $now >= $start_date;

$image_src = create_large_image_link($event->image_filename ?? "default.jpg");
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/components/head.php"; ?>
</head>
<body id="event-body">

<header>
    <?php include "components/navbar.php"; ?>
</header>
<div id="event-detail-content">
    <?= generateBreadcrumbs(["Home", "Event"]) ?>
    <div class="event-wrap">
        <main class="event-main">
            <img src="<?= $image_src ?>" alt="<?= htmlspecialchars($event->name) ?>" class="event-hero-image">

            <?php if ($is_registered): ?>
                <div class="status-badge status-badge-mobile badge-registered">Jste přihlášen(a)</div>
            <?php elseif ($event_started): ?>
                <div class="status-badge status-badge-mobile badge-closed">Událost již začala</div>
            <?php elseif ($is_past_deadline && !$is_full): ?>
                <div class="status-badge status-badge-mobile badge-closed">Registrace uzavřena</div>
            <?php elseif ($is_full): ?>
                <div class="status-badge status-badge-mobile badge-closed">Kapacita naplněna</div>
            <?php else: ?>
                <div class="status-badge status-badge-mobile badge-open">Registrace otevřena</div>
            <?php endif; ?>

            <div class="event-content-wrapper">
                <div id="countdown-container" class="countdown-box"
                     data-deadline="<?= $deadline->format('c') ?>"
                     data-start="<?= $start_date->format('c') ?>">
                    <div class="countdown-label" id="countdown-label">Zbývá času</div>
                    <div class="countdown-timer" id="countdown-timer">...</div>
                </div>
                <h1 class="event-title"><?= htmlspecialchars($event->name) ?></h1>
                <div class="event-description"><?= nl2br(htmlspecialchars($event->description ?? "Bez popisu.")) ?></div>
            </div>
        </main>

        <form class="event-sidebar" method="POST" id="registration-form">

            <input type="hidden" name="registerEventID" value="<?= $event->id ?>">
            <input type="hidden" name="registerUserID" value="<?= htmlspecialchars($current_user_id) ?>">
            <input type="hidden" name="registrationExists" value="<?= ($is_registered)?"true":"false" ?>">

            <?php if ($is_registered): ?>
                <div class="status-badge badge-registered">Jste přihlášen(a)</div>
            <?php elseif ($event_started): ?>
                <div class="status-badge badge-closed">Událost již začala</div>
            <?php elseif ($is_past_deadline && !$is_full): ?>
                <div class="status-badge badge-closed">Registrace uzavřena</div>
            <?php elseif ($is_full): ?>
                <div class="status-badge badge-closed">Kapacita naplněna</div>
            <?php else: ?>
                <div class="status-badge badge-open">Registrace otevřena</div>
            <?php endif; ?>

            <div class="info-row">
                <span class="info-label">Datum a čas</span>
                <span class="info-value"><?= $start_date->format('d. m. Y H:i') ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Konec registrací</span>
                <span class="info-value highlight <?= (!$is_past_deadline) ? 'past-deadline-active' : 'past-deadline-inactive' ?>">
                <?= $deadline->format('d. m. Y H:i') ?>
            </span>
            </div>

            <div class="info-row">
                <span class="info-label">Cena</span>
                <span class="info-value price"><?= ($price == 0) ? "ZDARMA" : htmlspecialchars($price) . " Kč" ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Místo konání</span>
                <span class="info-value"><?= $location ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Kapacita</span>
                <span class="info-value"><?= $occupancy ?> / <?= $capacity ?> účastníků</span>
                <?php $percent = ($capacity > 0) ? min(100, round(($occupancy / $capacity) * 100)) : 0; ?>
                <div class="capacity-bar-wrapper">
                    <div class="capacity-bar-fill" style="width: <?= $percent ?>%;"></div>
                </div>
                <div class="percent-status"><?= $percent ?>% zaplněno</div>
            </div>

            <?php if ($is_registered): ?>
                <?php if($is_past_deadline): ?>
                    <button type="submit" disabled name="action" value="remove" class="btn-action btn-locked">
                        Registrace ukončena
                    </button>
                <?php else: ?>
                    <button type="submit" <?php if($is_past_deadline): ?>disabled<?php endif; ?> name="action" value="remove" class="btn-action btn-cancel">
                        Zrušit registraci
                    </button>
                <?php endif; ?>

            <?php elseif (!$event_started): ?>
                <?php if (!$is_past_deadline && !$is_full): ?>
                    <button type="submit" name="action" value="add" class="btn-action btn-register">
                        Registrovat se
                    </button>
                <?php elseif ($is_full): ?>
                    <button type="button" class="btn-action btn-disabled">Plná kapacita</button>
                <?php else: ?>
                    <button type="button" class="btn-action btn-disabled">Registrace ukončena</button>
                <?php endif; ?>
            <?php else: ?>
                <button type="button" class="btn-action btn-disabled">Událost ukončena</button>
            <?php endif; ?>

        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // === COUNTDOWN ===
        const container = document.getElementById("countdown-container");
        const labelEl = document.getElementById("countdown-label");
        const timerEl = document.getElementById("countdown-timer");
        const deadlineTime = new Date(container.dataset.deadline).getTime();
        const startTime = new Date(container.dataset.start).getTime();

        function updateTimer() {
            const now = new Date().getTime();
            let targetTime;
            if (now < deadlineTime) {
                targetTime = deadlineTime; labelEl.textContent = "Konec registrace za:";
                labelEl.style.color = "#666"; timerEl.style.color = "var(--primary-kacubo)";
            } else if (now < startTime) {
                targetTime = startTime; labelEl.textContent = "Začátek události za:";
                labelEl.style.color = "#0f5132"; timerEl.style.color = "#333";
            } else {
                labelEl.textContent = "Stav události:"; timerEl.textContent = "Akce proběhla";
                timerEl.style.fontSize = "1.2rem"; return;
            }
            const dist = targetTime - now;
            const d = Math.floor(dist / (86400000));
            const h = Math.floor((dist % (86400000)) / (3600000)).toString().padStart(2,'0');
            const m = Math.floor((dist % (3600000)) / (60000)).toString().padStart(2,'0');
            const s = Math.floor((dist % (60000)) / 1000).toString().padStart(2,'0');
            timerEl.textContent = (d > 0) ? `${d}d ${h}:${m}:${s}` : `${h}:${m}:${s}`;
        }
        updateTimer(); setInterval(updateTimer, 1000);
    });
</script>

</body>
</html>