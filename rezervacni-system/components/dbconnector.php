<?php
function connect(){
    $servername = "localhost";
    $username = "hroudmi5";
    $password = "webove aplikace";
    $dbname = "hroudmi5";
    $conn = new mysqli($servername, $username, $password,$dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    //echo "Connected successfully";
    return $conn;
}
