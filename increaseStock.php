<?php
require_once '../dB/config.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['flowerID']) || !isset($data['quantity'])) {
    echo json_encode(["status" => "error", "message" => "Flower ID and quantity are required."]);
    exit;
}

$flowerID = (int)$data['flowerID'];
$quantityToAdd = (int)$data['quantity'];

// Update stock in database
$sql = "UPDATE flowers SET quantity = quantity + ? WHERE flowerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $quantityToAdd, $flowerID);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Stock updated successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update stock: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
