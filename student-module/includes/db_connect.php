<?php
/**
 * Database Connection using PDO
 * Secures the connection using prepared statements and robust error handling.
 */

$host = 'localhost';
$dbname = 'gec_placement_portal';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // Log the error securely and show a generic message to the user
    error_log("Database Connection Error: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}

