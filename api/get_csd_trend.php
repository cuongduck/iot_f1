<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';
$type = isset($_GET['type']) ? $_GET['type'] : 'all'; // all, co2, brix

try {
    // Lấy điều kiện thời gian
    $dateRangeQuery = getDateRangeQuery($period);
    
    // Số lượng điểm dữ liệu muốn lấy
    $limit = 60;
    if ($period == 'week' || $period == 'month') {
        $limit = 100;
    }
    
    // Query lấy dữ liệu từ bảng CSD_trend
    $query = "SELECT 
        Time, 
        Co2, 
        Brix,
        Speed
    FROM CSD_trend 
    $dateRangeQuery
    ORDER BY Time DESC 
    LIMIT $limit";

    $result = $conn->query($query);
    if (!$result) {
        throw new Exception($conn->error);
    }

    // Thu thập dữ liệu
    $times = [];
    $co2Values = [];
    $brixValues = [];
    $speedValues = [];

    while ($row = $result->fetch_assoc()) {
        // Định dạng thời gian
        $timestamp = strtotime($row['Time']);
        $formattedTime = date('H:i', $timestamp);
        
        if ($period == 'week' || $period == 'month') {
            $formattedTime = date('d/m H:i', $timestamp);
        }
        
        // Thêm vào mảng theo thứ tự thời gian tăng dần
        array_unshift($times, $formattedTime);
        array_unshift($co2Values, floatval($row['Co2']));
        array_unshift($brixValues, floatval($row['Brix']));
        array_unshift($speedValues, intval($row['Speed']));
    }
    
    // Xác định bộ dữ liệu cần trả về
    $response = [
        'times' => $times,
        'period' => $period
    ];
    
    if ($type == 'all' || $type == 'co2') {
        $response['co2'] = [
            'values' => $co2Values,
            'target' => 7.0,
            'min' => max(min($co2Values) - 0.5, 0),
            'max' => max($co2Values) + 0.5
        ];
    }
    
    if ($type == 'all' || $type == 'brix') {
        $response['brix'] = [
            'values' => $brixValues,
            'target' => 16.7,
            'min' => max(min($brixValues) - 0.5, 0),
            'max' => max($brixValues) + 0.5
        ];
    }
    
    if ($type == 'all' || $type == 'speed') {
        $response['speed'] = [
            'values' => $speedValues
        ];
    }
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>