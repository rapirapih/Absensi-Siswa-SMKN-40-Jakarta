<?php
include 'config.php';

// Cek apakah kolom sudah ada
$check = $conn->query("SHOW COLUMNS FROM absensi LIKE 'validasi_status'");
if ($check->num_rows == 0) {
    // Tambah kolom validasi_status dan validasi_catatan
    $sql = "ALTER TABLE absensi 
            ADD COLUMN validasi_status ENUM('pending', 'diterima', 'ditolak') DEFAULT 'pending',
            ADD COLUMN validasi_catatan TEXT NULL";

    if ($conn->query($sql)) {
        echo "Kolom validasi berhasil ditambahkan";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Kolom validasi sudah ada";
}
