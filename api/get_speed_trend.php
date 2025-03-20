<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php';

// Lấy tham số từ request
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    // Lấy điều kiện thời gian
    $dateRangeQuery = getDateRangeQuery($period);
    
    // Đếm tổng số bản ghi
    $countQuery = "SELECT COUNT(*) as total FROM CSD_trend $dateRangeQuery";
    $countResult = $conn->query($countQuery);
    
    if (!$countResult) {
        throw new Exception($conn->error);
    }
    
    $totalRecords = $countResult->fetch_assoc()['total'];
    
    // Query lấy dữ liệu từ bảng CSD_trend
    $query = "SELECT 
        id,
        Time, 
        Speed
    FROM CSD_trend 
    $dateRangeQuery
    ORDER BY Time ASC 
    LIMIT $limit OFFSET $offset";

    $result = $conn->query($query);
    if (!$result) {
        throw new Exception($conn->error);
    }

    // Thu thập dữ liệu
    $data = [];

    while ($row = $result->fetch_assoc()) {
        // Chuyển đổi thành timestamp JavaScript (milliseconds)
        $timestamp = strtotime($row["Time"]) * 1000;
        
        $data[] = [
            'id' => $row["id"],
            'time' => $row["Time"],
            'timestamp' => $timestamp,
            'speed' => (float)$row["Speed"]
        ];
    }
    
    // Chuẩn bị dữ liệu trả về
    $response = [
        'status' => 'success',
        'total' => $totalRecords,
        'limit' => $limit,
        'offset' => $offset,
        'period' => $period,
        'data' => $data
    ];
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>