<?php
// zugangsdaten für die uni datenbank
$host = "localhost";
$user = "g13";        
$pass = "dm38tan";    
$db   = "g13"; 

//lokal
//$host = "localhost";
//$user = "root";      
//$pass = "";          
//$db   = "shop_db";   

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "datenbank verbindung fehlgeschlagen"]));
}

// utf8 für umlaute
$conn->set_charset("utf8mb4");
