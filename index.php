<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireLogin();

include 'includes/header.php';

// Lấy factory từ parameter URL
$factory = isset($_GET['factory']) ? $_GET['factory'] : 'F3';

// Kiểm tra route
$page = isset($_GET['page']) ? $_GET['page'] : 'factory';

// Load trang tương ứng
switch($page) {
    case 'production_plan':
        include 'pages/production_plan.php';
        break;
    case 'factory':
        if ($factory == 'CSD') {
            include 'pages/line_csd.php';
        } elseif ($factory == 'FS') {
            include 'pages/line_fs.php';
        } else {
            include 'pages/line_csd.php'; // Mặc định nếu factory không hợp lệ
        }
        break;
    default:
        if ($factory == 'CSD') {
            include 'pages/line_csd.php';
        } elseif ($factory == 'FS') {
            include 'pages/line_fs.php';
        } else {
            include 'pages/line_csd.php'; // Mặc định nếu factory không hợp lệ
        }
        break;
}


?>