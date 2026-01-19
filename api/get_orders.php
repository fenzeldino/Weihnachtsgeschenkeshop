<?php
/*
  Gruppe: 13
  Mitglieder:  Daniel Menzel,Rohullah Sediqi, Tesch Etienne Mathis
  Beleg: Weihnachtsgeschenkeshop
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'db.php';

// neueste bestellungen oben
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);

$orders = [];
if ($result) {  
    while($row = $result->fetch_assoc()) {
        // kundendaten sind als json text gespeichert,
        $row['customer_data'] = json_decode($row['customer_data']);
        $orders[] = $row;
    }
}
// -> zurückwandeln damit vue sie lesen kann
echo json_encode($orders);
$conn->close();
?>