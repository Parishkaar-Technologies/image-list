<?php

// Database Connection (using PDO)
$host = 'localhost'; // Your MySQL host
$db = 'affiadmin_a1DB'; // Your database name
$user = 'affiadmin_Tarun'; // Your database user
$pass = 'mynewtargetis2500'; // Your database password
$dsn = "mysql:host=$host;dbname=$db;charset=utf8";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo; // Return the PDO object
} catch (PDOException $e) {
    die("Could not connect to the database $db :" . $e->getMessage());
}

?>