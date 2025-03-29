<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to Database
$conn = new mysqli("localhost", "root", "", "umalay");

// Check Connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Get JSON Data from Request
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['cart'])) {
    echo json_encode(["status" => "error", "message" => "Invalid cart data"]);
    exit;
}

foreach ($data['cart'] as $item) {
    $flowerID = (int) $item['id'];
    $quantity = (int) $item['quantity'];

    // Check Stock Availability
    $checkStockSQL = "SELECT quantity FROM flowers WHERE flowerID = ?";
    $stmt = $conn->prepare($checkStockSQL);
    $stmt->bind_param("i", $flowerID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row || $row['quantity'] < $quantity) {
        echo json_encode(["status" => "error", "message" => "Not enough stock for " . $item['name']]);
        exit;
    }

    // Deduct Quantity from Database
    $updateSQL = "UPDATE flowers SET quantity = quantity - ? WHERE flowerID = ?";
    $stmt = $conn->prepare($updateSQL);
    $stmt->bind_param("ii", $quantity, $flowerID);
    $stmt->execute();
}

// Close Connection
$conn->close();

echo json_encode(["status" => "success", "message" => "Order placed successfully!"]);
?>
