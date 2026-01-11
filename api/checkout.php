<?php
// damit keine fehler ins json rutschen
ob_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit;
}

// keine Warnungen im Output damit keine beschädigung des JSIN-Formats zustande kommen
error_reporting(0);

require_once 'db.php'; // DAtenbank verbindung
require_once 'stripe-php/init.php'; // Stripe Bibliothek


\Stripe\Stripe::setApiKey('sk_test_51Sf50LPfKVgT4yvvCI5xo9GviVESNd1BlTuMgDcWd7NRkxuSu8h0K9D8TgIlNPUwVEBSzgvgephBQ3wUQYVYcniB00DVPSBBer'); 

//JSON-Daten empfangen und in Php umwandeln
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['cart']) || empty($input['cart'])) {
    ob_end_clean(); // Puffer leeren
    http_response_code(400);
    echo json_encode(["error" => "leer"]);
    exit;
}

// Textstring für die Datenbankspalte 'customer_data'
$customer_json = isset($input['customer']) ? json_encode($input['customer'], JSON_UNESCAPED_UNICODE) : json_encode(["name" => "gast"]);

$line_items = []; // liste der Produkte für stripe
$total_amount = 0; // Gesamtsumme für die Datenbank

foreach ($input['cart'] as $item) {
    // preise und bestand direkt aus db laden
    $stmt = $conn->prepare("SELECT price, title, stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $item['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $product_db = $res->fetch_assoc();
    $stmt->close();
    
    if ($product_db) {
        $qty = (int)$item['qty']; // Menge aus dem Warenkorb
        $stock = (int)$product_db['stock']; // Bestand aus der Datenbank
        
        // Bestand DB muss größer/gleich der Meneg im Warenkorb sein
        if ($stock < $qty) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(["error" => "zu wenig bestand bei: " . $product_db['title']]);
            exit;
        }

        // reservieren (abziehen)
        $update = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $update->bind_param("ii", $qty, $item['id']);
        $update->execute();
        $update->close();

        // Cent weil Stripe mit Cent arbeitet
        $price_cent = (int)((float)$product_db['price'] * 100);
        $total_amount += ((float)$product_db['price'] * $qty);
        
        // Item-Objekt, für Stripe optimiert
        $line_items[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => $product_db['title']],
                'unit_amount' => $price_cent,
            ],
            'quantity' => $qty,
        ];
    }
}

// order speichern (Kundendaten & Gesamtpreis)
$insert = $conn->prepare("INSERT INTO orders (customer_data, total_price) VALUES (?, ?)");
$insert->bind_param("sd", $customer_json, $total_amount);
$insert->execute();
$insert->close();

try {
    // redirect url automatisch bestimmen
    if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
        $domain = 'http://localhost:5173';
    } else {
        $domain = 'https://ivm108.informatik.htw-dresden.de/ewa/g13/aplbeleg';
    }

    // Stripe Session erstellen
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => $domain . '/?status=success',
        'cancel_url' => $domain . '/?status=cancel',
    ]);

    ob_end_clean(); // Puffer leeren
    echo json_encode(['id' => $session->id]); // SessionId zurücksenden 

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
$conn->close();
?>