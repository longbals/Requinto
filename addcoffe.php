<?php
require_once '../dB/config.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['flower_name']) || empty($data['flower_name'])) {
    echo json_encode(["status" => "error", "message" => "Flower name is required."]);
    exit;
}

if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
    echo json_encode(["status" => "error", "message" => "Valid price is required."]);
    exit;
}

if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] < 0) {
    echo json_encode(["status" => "error", "message" => "Valid quantity is required."]);
    exit;
}

$flower_name = $conn->real_escape_string($data['flower_name']);
$price = (float)$data['price'];
$quantity = (int)$data['quantity'];

$sql = "INSERT INTO flowers (flower_name, price, quantity) VALUES ('$flower_name', $price, $quantity)";
if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => "Flower added successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add flower: " . $conn->error]);
}

$conn->close();
?>
