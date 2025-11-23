<?php

require_once __DIR__.'/../components/utils/links.php';
require_once __DIR__.'/../components/dbconnector.php';
require_once __DIR__.'/../components/check_auth.php';

check_auth_admin();
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    define("DELIMITER", ";");
    $filename = "event.csv";
    $file_content = "";

    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Content-Type: text/plain');
    header('Content-Length: ' . strlen($file_content));
    header('Connection: close');

    echo $file_content;
}else{
    redirect_to(create_error_link("404 stránka nenalezena"));
}
?>
