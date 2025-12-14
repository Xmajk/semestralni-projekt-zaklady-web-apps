<?php

namespace components\objects;

require_once __DIR__ . "/../../components/objects/User.php";
require_once __DIR__ . "/../../components/objects/Registration.php";
require_once __DIR__ . "/../../components/utils/links.php";
require_once __DIR__ . "/../../components/check_auth.php";
require_once __DIR__ . "/../../components/utils/date_time.php";

session_start();
check_auth_admin();
$redirectTo = $_SESSION["redirectto"]??createLink("/admin/index.php");

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $now = getDateTimeNow();
    $event_id = $_POST["event_id"];
    $user_username = $_POST["username"];
    $user=null;
    try {
        $user = User::getUserByUsername($user_username);
    } catch (\Exception $e) {
        redirectToDatabaseError();
    }
    if(is_null($user)) {
        $_SESSION["form_errors"] = [];
        $_SESSION["form_errors"]["r_username"] = "Uživatel neexistuje!";
        redirect_to($redirectTo);
    }
    else try {
        if (Registration::existsByUserIdAndEventId($user->id, $event_id)) {
            $_SESSION["form_errors"] = [];
            $_SESSION["form_errors"]["r_username"] = "Registrace již existuje!";
            redirect_to($redirectTo);
        }
    } catch (\Exception $e) {
        redirectToDatabaseError();
    }
    $registration = new Registration();
    $registration->id_event = $event_id;
    $registration->id_user = $user->id;
    $registration->registration_datetime = $now->format("Y-m-d H:i:s");
    $new = null;
    try {
        $new = Registration::createRegistration($registration);
    } catch (\Exception $e) {
        redirectToDatabaseError();
    }
    if(is_null($new)) {
        redirect_to(create_error_link("Registrace nebyla vytvořena"));
    }
    redirect_to($redirectTo);
}

?>