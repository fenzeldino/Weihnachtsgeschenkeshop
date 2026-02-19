<?php
/*
  Gruppe: 13
  Mitglieder:  Daniel Menzel,Rohullah Sediqi, Tesch Etienne Mathis
  Beleg: Weihnachtsgeschenkeshop
*/

require_once('/var/www/db_config.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "datenbank verbindung fehlgeschlagen"]));
}

// utf8 für umlaute
$conn->set_charset("utf8mb4");
