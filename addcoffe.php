<?php
require_once '../dB/config.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['coffee_name']) || empty($data['coffee_name'])) {
    echo json_encode(["status" => "error", "message" => "Coffee name is required."]);
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

$coffee_name = $conn->real_escape_string($data['coffee_name']);
$price = (float)$data['price'];
$quantity = (int)$data['quantity'];

$sql = "INSERT INTO coffee (coffee_name, price, quantity) VALUES ('$coffee_name', $price, $quantity)";
if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => "Coffee added successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add coffee: " . $conn->error]);
}

$conn->close();
?>
