<?php
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Handle validation submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $absensi_id = $_POST['absensi_id'];
    $status = $_POST['validasi_status'];
    $catatan = $_POST['validasi_catatan'];

    $sql = "UPDATE absensi SET validasi_status = ?, validasi_catatan = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $catatan, $absensi_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Status validasi berhasil diperbarui";
    } else {
        $_SESSION['error'] = "Gagal memperbarui status validasi";
    }
    
    header("Location: validasi_absensi.php");
    exit();
}

// Modify the query to get ALL attendance records with student info
$query = "SELECT a.*, s.nama, s.nis, s.kelas, s.jurusan 
          FROM absensi a 
          JOIN siswa s ON a.siswa_id = s.id 
          WHERE DATE(a.tanggal) = CURDATE()
          ORDER BY a.tanggal DESC";
$result = $conn->query($query);

if (!$result) {
    die("Error in query: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .img-preview {
            max-width: 200px;
            height: auto;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5em 1em;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Validasi Absensi Siswa</h2>
            <a href="dashboard_admin.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <?= htmlspecialchars($row['nama']) ?> 
                                    <small>(<?= htmlspecialchars($row['nis']) ?>)</small>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Kelas:</strong> <?= htmlspecialchars($row['kelas']) ?> - <?= htmlspecialchars($row['jurusan']) ?></p>
                                        <p><strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></p>
                                        <p><strong>Status:</strong> 
                                            <span class="badge <?php 
                                                switch($row['status']) {
                                                    case 'Hadir': echo 'bg-success'; break;
                                                    case 'Sakit': echo 'bg-warning text-dark'; break;
                                                    case 'Izin': echo 'bg-info'; break;
                                                    case 'Alpha': echo 'bg-danger'; break;
                                                    default: echo 'bg-secondary';
                                                }
                                            ?>">
                                                <?= $row['status'] ?>
                                            </span>
                                        </p>
                                        <p><strong>Status Validasi:</strong> 
                                            <span class="badge bg-<?= $row['validasi_status'] == 'diterima' ? 'success' : ($row['validasi_status'] == 'ditolak' ? 'danger' : 'warning') ?>">
                                                <?= ucfirst($row['validasi_status']) ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>

                                <form method="POST" class="mt-3">
                                    <input type="hidden" name="absensi_id" value="<?= $row['id'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Status Validasi</label>
                                        <select name="validasi_status" class="form-select" required>
                                            <option value="diterima">Terima</option>
                                            <option value="ditolak">Tolak</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        Submit Validasi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Tidak ada absensi yang perlu divalidasi hari ini.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
