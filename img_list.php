<?php

$pdo = include 'db_connect.php'; // Include and get the PDO object


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($pdo) { // Check if connection was successful
    try {
        $sql = "SELECT * FROM images";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Now you have your data in the $data variable.  Encode it to JSON:
            echo json_encode($data);
    
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    
        $pdo = null; // Close the connection (important!)
    
    } else {
        // Handle the case where the connection failed (db_connect.php likely died)
        echo "Database connection failed. Check your db_connect.php file.";
    }
    
?>