<?php
function connect(){
    $servername = "localhost";
    $username = "hroudmi5";
    $password = "webove aplikace";
    $dbname = "hroudmi5";

    try{
        $conn = new mysqli($servername, $username, $password,$dbname);
    }catch(Exception $e){
        die("Chyba komunikace serveru s databází");
    }
    return $conn;
}
