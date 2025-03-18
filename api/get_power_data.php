<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $dateRangeQuery = getDateRangeQuery($period);
    
    $query = "SELECT 
        SUM(COALESCE(F3_Thonggio_CS, 0)) as thong_gio,
        SUM(COALESCE(F3_VP, 0)) as van_phong,
        SUM(COALESCE(F3_MNK, 0)) as mnk,
        SUM(COALESCE(F3_AHU_Chiller, 0)) as ahu_chiller,
        SUM(COALESCE(F3_Kansui, 0)) as kansui,
        SUM(COALESCE(F3_Line_5, 0)) as line5,
        SUM(COALESCE(F3_Line_6, 0)) as line6,
        SUM(COALESCE(F3_Line_7, 0)) as line7,
        SUM(COALESCE(F3_Line_8, 0)) as line8,
        SUM(COALESCE(F3_Pho_1, 0)) as pho1,
        SUM(COALESCE(F3_Pho_2, 0)) as pho2,
        SUM(COALESCE(F3_Kho, 0)) as kho,
        SUM(COALESCE(F3_TramDien_Tong, 0)) as total
    FROM So_dien_F3
    $dateRangeQuery";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    $data = $result->fetch_assoc();
    
    // Chuy�6�9n t�5�9t c�5�7 gi�� tr�6�7 sang float
    $data = array_map('floatval', $data);
    
    // L�5�9y total t�6�9 F3_TramDien_Tong
    $total = $data['total'];
    unset($data['total']);
    
    // L�5�9y gi�� tr�6�7 cho bi�6�9u �0�4�6�5
    $values = array_values($data);
    
    // T��nh ph�6�1n tr�0�0m d�6�5a tr��n t�6�7ng c�6�5a F3_TramDien_Tong
    $percentages = array_map(function($value) use ($total) {
        return $total > 0 ? round(($value / $total) * 100, 1) : 0;
    }, $values);

    $response = [
        'labels' => [
            'Thong gio CS', 'Van phong', 'MNK', 'AHU Chiller', 
            'Kansui', 'Line 5', 'Line 6', 'Line 7', 'Line 8',
            'Pho 1', 'Pho 2', 'Kho'
        ],
        'values' => $values,
        'total' => $total,
        'percentages' => $percentages
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>