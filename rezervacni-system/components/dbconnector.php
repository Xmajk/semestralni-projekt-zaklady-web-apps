<?php
require_once __DIR__ . '/utils/links.php';

function connect(){
    $servername = "localhost";
    $username = "hroudmi5";
    $password = "webove aplikace";
    $dbname = "hroudmi5";

    try{
        $conn = new mysqli($servername, $username, $password,$dbname);
    }catch(Exception $e){
        redirect_to(create_error_link("Chyba databáze, kontaktujte správce"));
    }
    return $conn;
}
