<?php
include 'config.php';

if (!isset($_SESSION['siswa_id'])) {
    header("Location: login.php");
    exit();
}

// Get siswa data first - Move this up before we need to use $siswa_data
$siswa_id = $_SESSION['siswa_id'];
$siswa_query = $conn->prepare("SELECT * FROM siswa WHERE id = ?");
$siswa_query->bind_param("i", $siswa_id);
$siswa_query->execute();
$siswa_data = $siswa_query->get_result()->fetch_assoc();

// Validate that we got student data
if (!$siswa_data) {
    die("Error: Data siswa tidak ditemukan.");
}

// Add this after the session check
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nama = $_POST['nama'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $update_fields = [];
    $params = [];
    $types = "";

    // Update nama
    if (!empty($nama)) {
        $update_fields[] = "nama = ?";
        $params[] = $nama;
        $types .= "s";
    }

    // Update password if provided
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $error_message = "Password dan konfirmasi password tidak cocok!";
        } else {
            $update_fields[] = "password = ?";
            $params[] = $password; // In production, use password_hash()
            $types .= "s";
        }
    }

    // Handle photo upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error_message = "Format file tidak diizinkan. Gunakan JPG atau PNG.";
        } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $error_message = "Ukuran file terlalu besar. Maksimal 2MB.";
        } else {
            $new_filename = uniqid() . "." . $ext;
            $upload_path = "uploads/" . $new_filename;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                $update_fields[] = "gambar = ?";
                $params[] = $new_filename;
                $types .= "s";
            }
        }
    }

    // Proceed with update if there are no errors
    if (!isset($error_message) && !empty($update_fields)) {
        $params[] = $_SESSION['siswa_id'];
        $types .= "i";
        
        $sql = "UPDATE siswa SET " . implode(", ", $update_fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $success_message = "Profil berhasil diperbarui!";
            // Refresh siswa data
            $siswa_query->execute();
            $siswa_data = $siswa_query->get_result()->fetch_assoc();
        } else {
            $error_message = "Gagal memperbarui profil: " . $conn->error;
        }
    }
}

// Proses Input Absen
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['absen'])) {
    $status = $_POST['status'];
    
    // Cek apakah sudah absen hari ini
    $check_query = $conn->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND DATE(tanggal) = CURDATE()");
    $check_query->bind_param("i", $siswa_id);
    $check_query->execute();
    $check_result = $check_query->get_result();

    if ($check_result->num_rows == 0) {
        // Now we can safely use $siswa_data['nama'] since we got it earlier
        $insert_query = $conn->prepare("INSERT INTO absensi (siswa_id, nama_siswa, status, tanggal) VALUES (?, ?, ?, NOW())");
        $nama_siswa = $siswa_data['nama'];
        $insert_query->bind_param("iss", $siswa_id, $nama_siswa, $status);
        
        if ($insert_query->execute()) {
            $success_message = "Absensi berhasil dicatat!";
        } else {
            $error_message = "Gagal mencatat absensi: " . $conn->error;
        }
    } else {
        $error_message = "Anda sudah melakukan absensi hari ini.";
    }
}

// Modifikasi query riwayat absensi tanpa validasi
$absensi_query = $conn->prepare("
    SELECT status, tanggal
    FROM absensi 
    WHERE siswa_id = ? 
    ORDER BY tanggal DESC 
    LIMIT 10
");
$absensi_query->bind_param("i", $siswa_id);
$absensi_query->execute();
$absensi_result = $absensi_query->get_result();

// Cek status absensi hari ini
$today_query = $conn->prepare("SELECT status FROM absensi WHERE siswa_id = ? AND DATE(tanggal) = CURDATE()");
$today_query->bind_param("i", $siswa_id);
$today_query->execute();
$today_result = $today_query->get_result();
$sudah_absen = $today_result->num_rows > 0;
$status_hari_ini = $sudah_absen ? $today_result->fetch_assoc()['status'] : null;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-bg: #1a237e;
            --secondary-bg: #283593;
            --accent-color: #3949ab;
            --text-light: #ffffff;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        /* Modern Sidebar */
        .sidebar {
            background: linear-gradient(135deg, var(--primary-bg), var(--secondary-bg));
            min-height: 100vh;
            padding: 2rem 1.5rem;
            transition: var(--transition);
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        /* Main Content Area */
        .content {
            margin-left: 0; /* Remove left margin */
            padding: 2rem;
            transition: var(--transition);
            max-width: 1200px; /* Add max-width */
            margin: 0 auto; /* Center the content */
        }

        /* Modern Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            margin-bottom: 1.5rem;
            height: 100%; /* Ensure equal height */
        }

        /* Adjust card layouts */
        .row {
            margin: 0 -0.75rem; /* Negative margin for gutters */
        }

        .col-12,
        .col-md-6 {
            padding: 0 0.75rem; /* Even padding */
        }

        /* Header Styling */
        .main-header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Card Body Padding */
        .card-body {
            padding: 1.5rem;
            height: 100%; /* Full height */
            display: flex;
            flex-direction: column;
        }

        /* Table Container */
        .table-responsive {
            margin-top: auto; /* Push to bottom */
        }

        /* Status Badges */
        .badge {
            padding: 0.5em 1em;
            font-weight: 500;
            letter-spacing: 0.5px;
            border-radius: 6px;
        }

        /* Table Styling */
        .table {
            margin: 0;
        }

        .table thead th {
            background: var(--primary-bg);
            color: var(--text-light);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        /* Modern Buttons */
        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--primary-bg);
            border: none;
        }

        .btn-primary:hover {
            background: var(--secondary-bg);
            transform: translateY(-2px);
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: #c82333;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .content {
                padding: 1rem;
            }

            .card {
                margin-bottom: 1rem;
            }

            .main-header {
                padding: 1rem;
                margin-bottom: 1rem;
            }
        }

        /* Add these styles to your existing CSS */
        .modal-content {
            border: none;
            border-radius: 15px;
        }

        .modal-header {
            background: var(--primary-bg);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1rem 1.5rem;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid rgba(0,0,0,0.1);
            padding: 1rem 1.5rem;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(57, 73, 171, 0.25);
        }
    </style>
</head>
<body>

    <!-- Main Content -->
    <div class="content">
        <!-- Header -->
        <div class="main-header">
            <h4 class="mb-0">Dashboard Siswa</h4>
            <a href="logout.php" class="logout-btn">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>

        <!-- Add this right after the main-header div -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?= $error_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Absensi Form Card -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-calendar-check me-2"></i>Absensi Hari Ini
                        </h5>
                        
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success"><?= $success_message ?></div>
                        <?php endif; ?>
                        
                        <?php if ($sudah_absen): ?>
                            <div class="text-center p-4">
                                <h5>Status Absensi Hari Ini:</h5>
                                <span class="badge bg-primary fs-5 mt-2">
                                    <?= htmlspecialchars($status_hari_ini) ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Status Kehadiran</label>
                                    <select name="status" class="form-select" required>
                                        <option value="Hadir">Hadir</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Izin">Izin</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="absen" class="btn btn-primary">
                                        <i class="bi bi-check2-circle me-2"></i>Submit Absensi
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Info Siswa Card -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title">
                                <i class="bi bi-person-badge me-2"></i>Informasi Siswa
                            </h5>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="bi bi-pencil-square"></i> Edit Profil
                            </button>
                        </div>
                        <table class="table table-borderless">
                            <tr>
                                <td width="150">Nama</td>
                                <td>: <?= htmlspecialchars($siswa_data['nama']) ?></td>
                            </tr>
                            <tr>
                                <td>NIS</td>
                                <td>: <?= htmlspecialchars($siswa_data['nis']) ?></td>
                            </tr>
                            <tr>
                                <td>Kelas</td>
                                <td>: <?= htmlspecialchars($siswa_data['kelas']) ?></td>
                            </tr>
                            <tr>
                                <td>Jurusan</td>
                                <td>: <?= htmlspecialchars($siswa_data['jurusan']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Riwayat Absensi Card -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-clock-history me-2"></i>Riwayat Absensi
                        </h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $absensi_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                            <td>
                                                <span class="badge <?php
                                                    switch($row['status']) {
                                                        case 'Hadir': echo 'bg-success'; break;
                                                        case 'Sakit': echo 'bg-warning text-dark'; break;
                                                        case 'Izin': echo 'bg-info'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                ?>">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data" id="editProfileForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Foto Profil</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="<?= $siswa_data['gambar'] ? 'uploads/' . htmlspecialchars($siswa_data['gambar']) : 'assets/default-avatar.png' ?>" 
                                     alt="Foto Profil" 
                                     class="rounded-circle" 
                                     style="width: 64px; height: 64px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <input type="file" class="form-control" name="foto" accept="image/*">
                                    <small class="text-muted">Maksimal 2MB (JPG, PNG)</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($siswa_data['nama']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Konfirmasi password baru">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_profile" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>