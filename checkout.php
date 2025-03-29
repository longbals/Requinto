<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "requinto");  // Changed to 'requinto'

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['cart'])) {
    echo json_encode(["status" => "error", "message" => "Invalid cart data"]);
    exit;
}

foreach ($data['cart'] as $item) {
    $coffeeID = (int) $item['id'];
    $quantity = (int) $item['quantity'];

    $checkStockSQL = "SELECT quantity FROM coffeeshop WHERE coffeeID = ?";  // Assuming this table is correct
    $stmt = $conn->prepare($checkStockSQL);
    $stmt->bind_param("i", $coffeeID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row || $row['quantity'] < $quantity) {
        echo json_encode(["status" => "error", "message" => "Not enough stock for " . $item['name']]);
        exit;
    }

    $updateSQL = "UPDATE coffeeshop SET quantity = quantity - ? WHERE coffeeID = ?";
    $stmt = $conn->prepare($updateSQL);
    $stmt->bind_param("ii", $quantity, $coffeeID);
    $stmt->execute();
}

$conn->close();

echo json_encode(["status" => "success", "message" => "Order placed successfully!"]);
?>
