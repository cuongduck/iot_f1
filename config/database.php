<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'pnlekoychosting_mb');
define('DB_PASS', 'Masan@2025');
define('DB_NAME', 'pnlekoychosting_fs');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>