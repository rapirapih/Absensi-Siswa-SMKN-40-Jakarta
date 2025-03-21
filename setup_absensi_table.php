<?php
include 'config.php';

$sql = file_get_contents(__DIR__ . '/sql/setup_absensi_table.sql');

if($conn->multi_query($sql)) {
    echo "Absensi table setup successful!";
} else {
    echo "Error setting up absensi table: " . $conn->error;
}
?>
