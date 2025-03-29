<?php
require_once '../dB/config.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['flowerID']) || !isset($data['quantity'])) {
    echo json_encode(["status" => "error", "message" => "Flower ID and quantity are required."]);
    exit;
}

$flowerID = (int)$data['flowerID'];
$quantityToRemove = (int)$data['quantity'];

// Fetch current stock
$sqlCheck = "SELECT quantity FROM flowers WHERE flowerID = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i", $flowerID);
$stmtCheck->execute();
$result = $stmtCheck->get_result();
$stmtCheck->close();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Flower not found."]);
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
    // Update the flower's quantity
    $sqlUpdate = "UPDATE flowers SET quantity = ? WHERE flowerID = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ii", $newQuantity, $flowerID);
    if ($stmtUpdate->execute()) {
        echo json_encode(["status" => "success", "message" => "$quantityToRemove removed. New stock: $newQuantity"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update flower quantity: " . $conn->error]);
    }
    $stmtUpdate->close();
} else {
    // If quantity reaches zero, delete the flower
    $sqlDelete = "DELETE FROM flowers WHERE flowerID = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $flowerID);
    if ($stmtDelete->execute()) {
        echo json_encode(["status" => "success", "message" => "Flower removed completely!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to remove flower: " . $conn->error]);
    }
    $stmtDelete->close();
}

$conn->close();
?>
