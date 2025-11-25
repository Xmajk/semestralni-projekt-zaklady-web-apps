<?php
use components\objects\Event;
require_once __DIR__ . "/../../components/objects/Event.php";
require_once __DIR__ . "/../../components/utils/links.php";
require_once __DIR__ . "/../../components/check_auth.php";

session_start();
check_auth_admin();

if($_SERVER["REQUEST_METHOD"] == "GET") {
    $event_id = $_REQUEST["id"]??null;
    if($event_id === null || !is_numeric($event_id)) {
        redirect_to(create_error_link("Nesprávný formát id"));
    }

    $event_id = intval($event_id);
    $event = Event::GetByID($event_id);
    if($event !== null) {
        Event::deleteById($event_id);
    }
    redirect_to(createLink("/admin/events.php"));
}

http_response_code(404);
echo "404 Not Found";
?>
