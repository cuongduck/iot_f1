<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';
$type = isset($_GET['type']) ? $_GET['type'] : 'chart'; // chart, table

try {
    // Lấy điều kiện thời gian
    $originalDateRangeQuery = getDateRangeQuery($period);
    $dateRangeQuery = str_replace('Time', 'Date', $originalDateRangeQuery);
    
    if ($type == 'chart') {
        // Query cho biểu đồ - tính tổng thời gian dừng theo lỗi
        $query = "SELECT 
            Ten_Loi as ErrorName,
            SUM(Thoi_Gian_Dung) as Duration,
            GROUP_CONCAT(Ghi_Chu SEPARATOR '; ') as Details
        FROM Downtime 
        $dateRangeQuery
        AND Line = 'CSD'
        GROUP BY Ten_Loi
        ORDER BY Duration DESC";
        
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception($conn->error);
        }
        
        $chartData = [];
        $totalDuration = 0;
        
        while ($row = $result->fetch_assoc()) {
            $totalDuration += floatval($row['Duration']);
            $chartData[] = [
                'name' => $row['ErrorName'] ?: 'Không xác định',
                'value' => floatval($row['Duration']),
                'details' => $row['Details']
            ];
        }
        
        echo json_encode([
            'data' => $chartData,
            'totalDuration' => $totalDuration,
            'period' => $period
        ]);
    } else {
        // Query cho bảng - lấy chi tiết từng lần dừng
        $query = "SELECT 
            ID, 
            Date, 
            Line, 
            Ten_Loi, 
            Thoi_Gian_Dung, 
            Ghi_Chu, 
            Created_At
        FROM Downtime 
        $dateRangeQuery
        AND Line = 'CSD'
        ORDER BY Date DESC";
        
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception($conn->error);
        }
        
        $tableData = [];
        
        while ($row = $result->fetch_assoc()) {
            $tableData[] = [
                'id' => $row['ID'],
                'date' => $row['Date'],
                'error' => $row['Ten_Loi'] ?: 'Không xác định',
                'duration' => floatval($row['Thoi_Gian_Dung']),
                'note' => $row['Ghi_Chu'],
                'created' => $row['Created_At']
            ];
        }
        
        echo json_encode([
            'data' => $tableData,
            'period' => $period
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>