<?php
include 'config.php';
header('Content-Type: application/json');

date_default_timezone_set('Asia/Jakarta');
$current_time = date('H:i');
$current_date = date('Y-m-d');

if ($current_time >= '17:00') {
    // Check if data has already been reset today
    $check_query = "SELECT COUNT(*) as count FROM absensi WHERE DATE(tanggal) = CURDATE()";
    $check_result = $conn->query($check_query);
    $row = $check_result->fetch_assoc();
    
    if ($row['count'] > 0) {
        // Reset attendance data
        $reset_query = "DELETE FROM absensi WHERE DATE(tanggal) = CURDATE()";
        if ($conn->query($reset_query)) {
            echo json_encode([
                'should_notify' => true,
                'message' => 'Data absensi hari ini telah direset.',
                'status' => 'success'
            ]);
            exit;
        } else {
            echo json_encode([
                'should_notify' => true,
                'message' => 'Gagal mereset data absensi: ' . $conn->error,
                'status' => 'error'
            ]);
            exit;
        }
    }
}

echo json_encode(['should_notify' => false]);
?>