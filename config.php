<?php

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$username = "root";
$password = "";
$database = "requinto";  // Changed from 'umalay' to 'requinto'

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) { 
    die("Database Connection Failed: " . $conn->connect_error);
}

?>
