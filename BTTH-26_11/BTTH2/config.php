<?php
$host = 'localhost';
$user = 'root';
$pass = 'Dk@17092004';
$db   = 'qizz_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
