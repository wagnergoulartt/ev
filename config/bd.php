<?php
// Database configuration
$host = "localhost";     // Database host
$username = "root";      // Database username
$password = "";         // Database password
$database = "nome_do_banco";  // Database name

// Create database connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
