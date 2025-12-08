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
    $registration_id = $_REQUEST["registration_id"];
    if(is_null($registration_id)) {
        redirect_to(create_error_link("Registrace nenalezena"));
    }
    $registration = Registration::getRegistrationById($registration_id);
    Registration::deleteRegistration($registration);
    redirect_to($redirectTo);
}

?>