<?php
include 'config.php';

// Buat tabel login_guru jika belum ada
$sql = "CREATE TABLE IF NOT EXISTS login_guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL
)";

if ($conn->query($sql)) {
    echo "Tabel login_guru berhasil dibuat.<br>";
    
    // Insert akun guru default
    $username = 'guru';
    $password = 'guru123';
    $nama = 'Guru Default';
    
    // Cek apakah username sudah ada
    $check = $conn->query("SELECT * FROM login_guru WHERE username = '$username'");
    if ($check->num_rows == 0) {
        // Insert akun guru baru
        $sql = "INSERT INTO login_guru (username, password, nama) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $password, $nama);
        $stmt->execute();
        echo "Akun guru berhasil ditambahkan.<br>";
    } else {
        echo "Akun guru sudah ada.<br>";
    }
} else {
    echo "Error creating table: " . $conn->error;
}
