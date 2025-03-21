<?php
include 'config.php';

// Check if keterangan column exists
$check = $conn->query("SHOW COLUMNS FROM absensi LIKE 'keterangan'");
if ($check->num_rows == 0) {
    // Add keterangan column if it doesn't exist
    $sql = "ALTER TABLE absensi ADD COLUMN keterangan TEXT NULL AFTER status";
    if($conn->query($sql)) {
        echo "Keterangan column added successfully";
    } else {
        echo "Error adding keterangan column: " . $conn->error;
    }
} else {
    echo "Keterangan column already exists";
}
?>
