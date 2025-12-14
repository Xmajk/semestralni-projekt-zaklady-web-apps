<?php
/**
 * Main Event Listing Page
 *
 * This script serves as the homepage for the application. It displays a paginated
 * list of events, visualizes the user's registration status (joined/available/locked),
 * and handles navigation.
 */

use components\objects\Event;
use components\objects\Registration;

require_once __DIR__ . "/components/breadcrumb.php";

/**
 * Defines the number of event cards displayed per pagination page.
 */
const EVENTS_PER_PAGE = 6;

require_once __DIR__ . "/components/objects/Event.php";
require_once __DIR__ . "/components/objects/Registration.php";
require_once __DIR__ . "/components/check_auth.php";
require_once __DIR__ . "/components/utils/date_time.php";
require_once __DIR__ . "/components/utils/links.php";
require_once __DIR__ . "/components/utils/numbers.php";

/**
 * Checks if the registration period for an event has closed.
 *
 * Compares the current server time against the event's registration deadline.
 *
 * @param Event $event The event object containing the deadline.
 * @return bool True if the deadline has passed (locked), false otherwise.
 */
function eventIsLocked(Event $event): bool {
    $event_deadline = convertStringToDateTime($event->registration_deadline);
    $now = new DateTime();
    return ($event_deadline->getTimestamp() - $now->getTimestamp()) <= 0;
}

/**
 * Determines if a specific user is already registered for a specific event.
 *
 * Acts as a safe wrapper around the Registration::existsByUserIdAndEventId method,
 * handling potential database exceptions by redirecting to an error page.
 *
 * @param int $user_id The unique ID of the user.
 * @param int $event_id The unique ID of the event.
 * @return bool True if a registration record exists, false otherwise.
 */
function userIsRegistered(int $user_id, int $event_id): bool {
    $result = false;
    try {
        $result = Registration::existsByUserIdAndEventId($user_id, $event_id);
    } catch (Exception $exception) {
        redirect_to(create_error_link("Chyba databáze"));
    }
    return $result;
}

session_start();
check_auth_user();

$error = "";
$now = new DateTime();
$user_id = isset($_COOKIE["user_id"]) ? (int)$_COOKIE["user_id"] : 0;
$numberOfEvents = 0;

try {
    $numberOfEvents = Event::countEvents();
} catch (Exception $exception) {
    redirectToDatabaseError();
}

// Calculate total pages
$numberOfPages = ceil($numberOfEvents / EVENTS_PER_PAGE);

// Handle current page input
$currentPage = $_GET["page"] ?? "1";

if (!is_numeric($currentPage) && $currentPage != "max") {
    redirect_to(create_error_link("Chybné číslo stránky"));
} else if ($currentPage == "max") {
    $currentPage = $numberOfPages;
}

$currentPage = intval($currentPage);

if ($currentPage < 1) {
    redirect_to(create_error_link("Chybné číslo stránky"));
}

if ($currentPage > $numberOfPages) {
    $currentPage = intval($numberOfPages);
}

// Build pagination links (Previous, Current, Next)
$pagination = [];
if ($currentPage != 1) {
    $pagination[] = $currentPage - 1;
}
$pagination[] = $currentPage;
if ($currentPage != $numberOfPages) {
    $pagination[] = $currentPage + 1;
}

// Fetch events for the current view
$events = [];
try {
    $events = Event::getPage(EVENTS_PER_PAGE, $currentPage);
} catch (Exception $exception) {
    redirectToDatabaseError();
}

$userEvents = [];
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/components/head.php" ?>
</head>
<body id="home-body">
<header>
    <?php include "components/navbar.php"; ?>
</header>
<?= generateBreadcrumbs(["Home"]) ?>
<div class="events-container">
    <?php if ($numberOfEvents > 0): ?>
        <?php foreach ($events as $event): ?>
            <div class="event-card <?php
            if (userIsRegistered($user_id, $event->id)) {
                echo "joined";
            } else if (eventIsLocked($event)) {
                echo "locked";
            } else {
                echo "available";
            };
            ?>">
                <div class="stat-tape"></div>
                <img alt="Event image" class="event-image" src="<?= create_small_image_link($event->image_filename ?? "default.jpg") ?>">
                <div class="event-content">
                    <div class="event-title"><?= htmlspecialchars($event->name) ?></div>
                    <div class="event-date">Datum konání: <?= htmlspecialchars(convertStringToDateTime($event->start_datetime)->format("d. m. Y H:i")) ?></div>
                    <div class="event-date">Registrace do: <?= htmlspecialchars(convertStringToDateTime($event->registration_deadline)->format("d. m. Y H:i")) ?></div>
                    <div class="event-location"><?= htmlspecialchars($event->location) ?></div>
                    <div class="event-description">
                        <?= htmlspecialchars($event->description) ?>
                    </div>
                </div>
                <div class="event-actions">
                    <a class="link-as-btn rounded" href="<?= createLink("/event.php?" . http_build_query(["id" => $event->id])) ?>">Více informací</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nejsou žádné události</p>
    <?php endif ?>

</div>
<?php if ($numberOfPages > 1): ?>
    <div class="pagination-wrap">
        <ul class="pagination">
            <li>
                <a href="<?= createLink("/index.php?" . http_build_query(["page" => 1])) ?>">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="visuallyhidden">previous set of pages</span>
                </a>
            </li>

            <?php foreach ($pagination as $page): ?>
                <li>
                    <a href="<?= createLink("/index.php?" . http_build_query(["page" => $page])) ?>"
                            <?php if ($currentPage == $page): ?>
                                aria-current="page"
                            <?php endif ?>
                    ><span class="visuallyhidden">page </span><?= $page ?></a>
                </li>
            <?php endforeach ?>
            <li>
                <a href="<?= createLink("/index.php?" . http_build_query(["page" => "max"])) ?>">
            <span class="visuallyhidden">next set of pages</span
            ><span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </div>
<?php endif ?>
</body>
</html>