<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Hapus data berdasarkan ID
    $stmt = $conn->prepare("DELETE FROM guru WHERE nip = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Data berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus data.";
    }

    header("Location: guru.php");
    exit();
} else {
    $_SESSION['error'] = "ID tidak ditemukan.";
    header("Location: guru.php");
    exit();
}
?>
