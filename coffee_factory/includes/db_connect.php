<?php
require_once __DIR__ . '/../config.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_factory_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>