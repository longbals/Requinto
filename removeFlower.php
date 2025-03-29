<?php
require_once '../dB/config.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['coffeeID']) || !isset($data['quantity'])) {
    echo json_encode(["status" => "error", "message" => "Coffee ID and quantity are required."]);
    exit;
}

$coffeeID = (int)$data['coffeeID'];
$quantityToRemove = (int)$data['quantity'];

// Fetch current stock
$sqlCheck = "SELECT quantity FROM coffeeshop WHERE coffeeID = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i", $coffeeID);
$stmtCheck->execute();
$result = $stmtCheck->get_result();
$stmtCheck->close();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Coffee not found."]);
    exit;
}

$row = $result->fetch_assoc();
$currentQuantity = (int)$row['quantity'];

// Ensure quantity to remove is valid
if ($quantityToRemove <= 0 || $quantityToRemove > $currentQuantity) {
    echo json_encode(["status" => "error", "message" => "Invalid quantity entered."]);
    exit;
}

$newQuantity = $currentQuantity - $quantityToRemove;

if ($newQuantity > 0) {
    // Update the coffee's quantity
    $sqlUpdate = "UPDATE coffeeshop SET quantity = ? WHERE coffeeID = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ii", $newQuantity, $coffeeID);
    if ($stmtUpdate->execute()) {
        echo json_encode(["status" => "success", "message" => "$quantityToRemove removed. New stock: $newQuantity"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update coffee quantity: " . $conn->error]);
    }
    $stmtUpdate->close();
} else {
    // If quantity reaches zero, delete the coffee
    $sqlDelete = "DELETE FROM coffeeshop WHERE coffeeID = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $coffeeID);
    if ($stmtDelete->execute()) {
        echo json_encode(["status" => "success", "message" => "Coffee removed completely!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to remove coffee: " . $conn->error]);
    }
    $stmtDelete->close();
}

$conn->close();
?>
