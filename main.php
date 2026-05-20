<?php
// main.php - PDO database connection (used by all backend files)

// Database configuration
$host = "localhost";      // XAMPP default
$dbname = "taskifydb"; // your database name
$user = "root";           // XAMPP default
$pass = "";               // XAMPP default

try {
    // Create PDO instance
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch associative arrays
            PDO::ATTR_EMULATE_PREPARES => false               // Disable emulated prepares
        ]
    );
} catch (PDOException $e) {
    // If connection fails, stop execution
    die("Database connection failed: " . $e->getMessage());
}
