<?php
include 'config.php';

// Create validation columns
$sql = "ALTER TABLE absensi 
        ADD COLUMN IF NOT EXISTS validasi_status ENUM('pending', 'diterima', 'ditolak') DEFAULT 'pending',
        ADD COLUMN IF NOT EXISTS validasi_catatan TEXT NULL";

if($conn->query($sql)) {
    // Update all existing records to have 'pending' status
    $update = "UPDATE absensi SET validasi_status = 'pending' WHERE validasi_status IS NULL";
    if($conn->query($update)) {
        echo "Setup successful - Validation columns added and existing records updated";
    } else {
        echo "Error updating records: " . $conn->error;
    }
} else {
    echo "Error creating columns: " . $conn->error;
}
