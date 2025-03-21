<?php
include 'config.php';

// ...existing code or session validation if needed...

// Hapus semua data guru
$conn->query("DELETE FROM guru");

// Reset AUTO_INCREMENT ke 1
$conn->query("ALTER TABLE guru AUTO_INCREMENT = 1");

header("Location: data_guru.php");
exit();
?>
