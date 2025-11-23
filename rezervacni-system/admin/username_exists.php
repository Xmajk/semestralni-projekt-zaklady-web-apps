<?php
use components\objects\User;
require_once __DIR__ . "/../components/objects/User.php";
require_once __DIR__ . "/../components/check_auth.php";

check_auth_admin();
$query_username = $_GET["username"]??null;
if (is_null($query_username) || trim($query_username) == "") {
    echo "Invalide input parametr username";
    http_response_code(400);
    return;
}
try{
    echo User::check_username($query_username)?"true":"false";
    http_response_code(200);
}catch (Exception $e){
    echo $e->getMessage();
    http_response_code(500);
}

?>