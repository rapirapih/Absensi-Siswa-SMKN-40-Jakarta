<?php
require 'vendor/autoload.php'; // Harus ada untuk PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Koneksi ke database
$koneksi = new mysqli("localhost", "root", "", "absensi_siswa");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Ambil data dari database
$query = "SELECT id, siswa_id, tanggal, status FROM absensi";
$result = $koneksi->query($query);
if (!$result) {
    die("Query error: " . $koneksi->error);
}

// Buat Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header kolom
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Nama Siswa');
$sheet->setCellValue('C1', 'Tanggal');
$sheet->setCellValue('D1', 'Status');

// Mengisi data dari database
$row = 2;
while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $data['id']);
    $sheet->setCellValue('B' . $row, $data['nama_siswa']);
    $sheet->setCellValue('C' . $row, $data['tanggal']);
    $sheet->setCellValue('D' . $row, $data['status']);
    $row++;
}

// Set header untuk download file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Data_Absensi.xlsx"');
header('Cache-Control: max-age=0');


ob_clean(); // Hapus buffer output sebelum header
flush(); // Kirim buffer yang tersisa
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
