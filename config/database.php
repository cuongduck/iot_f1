<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'pnlekoychostdsdsd');
define('DB_PASS', 'Masadsds');
define('DB_NAME', 'pnlekoychodsd');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>