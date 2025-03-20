<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'pnlekoychdb');
define('DB_PASS', 'Masd');
define('DB_NAME', 'pnlekod');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>