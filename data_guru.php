<?php
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Ambil data guru dari database (pastikan nama kolom benar)
$guru_result = $conn->query("SELECT * FROM guru ORDER BY id_guru ASC");

if (!$guru_result) {
    die("Query error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            margin-top: 20px;
        }
        .table img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mt-4 mb-4">📖 Data Guru</h2>
    
    <a href="dashboard_admin.php" class="btn btn-primary mb-3">Kembali</a>
    
    <?php if ($guru_result->num_rows > 0) { ?>
        <table class="table table-striped table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Mata Pelajaran</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = $guru_result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['nip']) ?></td>
                    <td><?= htmlspecialchars($row['mata_pelajaran']) ?></td>
                    <td>
                        <?php if (!empty($row['gambar'])) { ?>
                            <img src="uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama']) ?>">
                        <?php } else { ?>
                            <span>Tidak ada foto</span>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="alert alert-warning">Tidak ada data guru.</div>
    <?php } ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
