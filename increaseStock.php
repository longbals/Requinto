<?php
require_once '../dB/config.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['coffeeID']) || !isset($data['quantity'])) {
    echo json_encode(["status" => "error", "message" => "Coffee ID and quantity are required."]);
    exit;
}

$coffeeID = (int)$data['coffeeID'];
$quantityToAdd = (int)$data['quantity'];

// Update stock in database
$sql = "UPDATE coffeeshop SET quantity = quantity + ? WHERE coffeeID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $quantityToAdd, $coffeeID);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Stock updated successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update stock: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
