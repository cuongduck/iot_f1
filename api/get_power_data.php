<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $dateRangeQuery = getDateRangeQuery($period);
    
    $query = "SELECT 
        SUM(COALESCE(CSD_RO, 0)) as RO,
        SUM(COALESCE(PET_BlowMoulder, 0)) PET_Blow,
        SUM(COALESCE(PET_Chiller, 0)) as PET_Chiller,
        SUM(COALESCE(F1_DG_CSD, 0)) as DG_CSD,
        SUM(COALESCE(F1_MCC_CSD, 0)) as MCC_CSD,
        SUM(COALESCE(F1_MNK_PET, 0)) as MNK_Pet,
        SUM(COALESCE(F1_ChillerCSD, 0)) as CSD_Chiller,
        SUM(COALESCE(F1_DieuHoa_CSD, 0)) as CSD_DH,
        SUM(COALESCE(F1_MNK_75, 0)) as MNK_75,
        SUM(COALESCE(CSD_AHU, 0)) as CSD_AHU,
        SUM(COALESCE(F1_Kho, 0)) as Kho,
        SUM(COALESCE(F1_VP, 0)) as VP,
        SUM(COALESCE(F1_MDB2_1_1, 0)) as total
    FROM CSD_So_Dien
    $dateRangeQuery";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    $data = $result->fetch_assoc();
    
    // Chuyển tất cả giá trị sang float
    $data = array_map('floatval', $data);
    
    // Lấy total từ F1_MDB2_1_1
    $total = $data['total'];
    unset($data['total']);
    
    // Lấy giá trị cho biểu đồ
    $values = array_values($data);
    
    // Tính phần trăm dựa trên tổng của F1_MDB2_1_1
    $percentages = array_map(function($value) use ($total) {
        return $total > 0 ? round(($value / $total) * 100, 1) : 0;
    }, $values);

    $response = [
        'labels' => [
            'RO', 'PET_Blow', 'PET_Chiller', 'DG_CSD', 
            'VP', 'MCC_CSD', 'MNK_Pet', 'CSD_Chiller', 'CSD_DH',
            'MNK_75', 'CSD_AHU', 'Kho'
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