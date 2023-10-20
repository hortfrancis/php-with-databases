<?php 

// Set database credentials 
$server = 'localhost';
$username = 'root';
$password = 'pw123';
$dbname = 'database';

try {
    // Attempt to connect to the SQL database
    $conn = new PDO("mysql:host=$server;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo 'Successfully connected to database';

} catch(PDOException $e) {
    // Error handling
    echo 'Connection to database failed: ' . $e->getMessage();
    exit;
}



