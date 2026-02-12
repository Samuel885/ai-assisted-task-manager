<?php
// config.php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "taskbalance_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}
?>
