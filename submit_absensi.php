<?php
include 'config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['siswa_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada sesi login']);
    exit();
}

$siswa_id = $_SESSION['siswa_id'];
date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');
$current_time = date('H:i:s');

// Create table if not exists
$create_table_query = "CREATE TABLE IF NOT EXISTS absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    tanggal DATE NOT NULL,
    waktu TIME NOT NULL,
    status ENUM('Hadir', 'Izin', 'Sakit', 'Alpha') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id_siswa)
)";
$conn->query($create_table_query);

// Check if already attended
$check_query = $conn->prepare("SELECT * FROM absensi WHERE siswa_id = ? AND tanggal = ?");
$check_query->bind_param("is", $siswa_id, $today);
$check_query->execute();

if ($check_query->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Anda sudah absen hari ini']);
    exit();
}

// Insert new attendance
$insert_query = $conn->prepare("INSERT INTO absensi (siswa_id, tanggal, waktu, status) VALUES (?, ?, ?, 'Hadir')");
$insert_query->bind_param("iss", $siswa_id, $today, $current_time);

if ($insert_query->execute()) {
    echo json_encode(['success' => true, 'message' => 'Absensi berhasil']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan absensi']);
}
?>

<script>
document.getElementById("absen-button").addEventListener("click", function() {
    if (confirm('Apakah Anda yakin ingin melakukan absensi sekarang?')) {
        fetch("submit_absensi.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: 'submit_attendance'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("absen-status").innerText = "Sudah absen";
                document.getElementById("absen-button").disabled = true;
                alert('Absensi berhasil dicatat!');
            } else {
                alert("Gagal absen: " + data.message);
            }
        })
        .catch(error => {
            alert("Terjadi kesalahan: " + error);
        });
    }
});
</script>