<?php 
$server = 'localhost';
$username = 'root';
$password = 'pw123';
$dbname = 'database';

try {
    $conn = new PDO("mysql:host=$server;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo 'Successfully connected to database';
} 
catch(PDOException $e) {
    echo 'Connection to database failed: ' . $e->getMessage();
}