<?php
// filepath: /c:/laragon/www/absensi_siswa/siswa.php
include 'config.php';

// Pastikan sesi hanya dimulai jika belum aktif
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Tambah Siswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_siswa'])) {
    $nama = $_POST['nama'];
    $nis = $_POST['nis']; // Tambahkan ini
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];

    // Proses upload gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder uploads jika belum ada
        }
        $file_name = basename($_FILES['gambar']['name']);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi tipe file
        if ($imageFileType == "jpg" || $imageFileType == "jpeg" || $imageFileType == "png") {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                // File berhasil diupload
                $gambar = $file_name;
            } else {
                die("Error uploading file.");
            }
        } else {
            die("Hanya file JPG, JPEG, dan PNG yang diperbolehkan.");
        }
    } else {
        $gambar = null; // Jika tidak ada file yang diupload
    }

    // Simpan data ke database
    $sql = "INSERT INTO siswa (nama, nis, kelas, jurusan, gambar) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nama, $nis, $kelas, $jurusan, $gambar);
    $stmt->execute();
    $_SESSION['message'] = "Siswa berhasil ditambahkan.";
    header("Location: siswa.php");
    exit();
}

// Edit Siswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_siswa'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $nis = $_POST['nis']; // Tambahkan ini
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];

    // Proses upload gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder uploads jika belum ada
        }
        $file_name = basename($_FILES['gambar']['name']);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi tipe file
        if ($imageFileType == "jpg" || $imageFileType == "jpeg" || $imageFileType == "png") {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                // File berhasil diupload
                $gambar = $file_name;
            } else {
                die("Error uploading file.");
            }
        } else {
            die("Hanya file JPG, JPEG, dan PNG yang diperbolehkan.");
        }
    } else {
        $gambar = $_POST['existing_gambar']; // Jika tidak ada file yang diupload, gunakan gambar yang sudah ada
    }

    // Update data di database
    $sql = "UPDATE siswa SET nama = ?, nis = ?, kelas = ?, jurusan = ?, gambar = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nama, $nis, $kelas, $jurusan, $gambar, $id);
    $stmt->execute();
    $_SESSION['message'] = "Siswa berhasil diperbarui.";
    header("Location: siswa.php");
    exit();
}

// Hapus Siswa
if (isset($_GET['delete_siswa'])) {
    $id = $_GET['delete_siswa'];
    
    // First check if student exists
    $check = $conn->prepare("SELECT id FROM siswa WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $sql = "DELETE FROM siswa WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Perbaiki urutan ID setelah penghapusan
            $conn->query("SET @num := 0");
            $conn->query("UPDATE siswa SET id = (@num := @num + 1)");
            $conn->query("ALTER TABLE siswa AUTO_INCREMENT = 1");
            
            $_SESSION['message'] = "Siswa berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus data siswa.";
        }
    } else {
        $_SESSION['error'] = "Data siswa tidak ditemukan.";
    }
    
    header("Location: siswa.php");
    exit();
}

// Ambil data siswa dengan ID yang berurutan
$result = $conn->query("SELECT * FROM siswa ORDER BY id ASC");

// Ambil data siswa yang akan diedit
$edit_row = null;
if (isset($_GET['edit_siswa'])) {
    $id = $_GET['edit_siswa'];
    $edit_result = $conn->query("SELECT * FROM siswa WHERE id = $id");
    $edit_row = $edit_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">📚 Data Siswa</h2>
    <!-- Tombol Kembali -->
    <div class="d-flex justify-content-end mb-3">
        <a href="dashboard_admin.php"><button class="btn btn-secondary">Kembali</button></a>
    </div>
    <!-- Notifikasi -->
    <?php if (isset($_SESSION['message'])) { ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php } ?>
    <!-- Form Tambah/Edit Siswa -->
    <div class="card shadow-sm p-4 mt-3">
        <h5 class="card-title"><?= isset($edit_row) ? 'Edit Siswa' : 'Tambah Siswa' ?></h5>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= isset($edit_row) ? $edit_row['id'] : '' ?>">
            <input type="hidden" name="existing_gambar" value="<?= isset($edit_row) ? $edit_row['gambar'] : '' ?>">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" value="<?= isset($edit_row) ? $edit_row['nama'] : '' ?>" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="nis" class="form-control" placeholder="NIS" value="<?= isset($edit_row) ? $edit_row['nis'] : '' ?>" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="kelas" class="form-control" placeholder="Kelas" value="<?= isset($edit_row) ? $edit_row['kelas'] : '' ?>" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="jurusan" class="form-control" placeholder="Jurusan" value="<?= isset($edit_row) ? $edit_row['jurusan'] : '' ?>" required>
                </div>
                <div class="col-md-2">
                    <input type="file" name="gambar" class="form-control" accept="image/*">
                </div>
                <div class="col-md-12 mt-3">
                    <button type="submit" name="<?= isset($edit_row) ? 'update_siswa' : 'add_siswa' ?>" class="btn btn-primary w-100"><?= isset($edit_row) ? 'Update' : 'Tambah' ?></button>
                </div>
            </div>
        </form>
    </div>
    <!-- Tabel Data Siswa -->
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="card-title">Daftar Siswa</h5>
            <table class="table table-striped table-bordered mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>NIS</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['nis']) ?></td>
                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                        <td><?= htmlspecialchars($row['jurusan']) ?></td>
                        <td>
                            <?php if ($row['gambar']) { ?>
                                <img src="uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama']) ?>" width="50">
                            <?php } else { ?>
                                <span>Tidak ada gambar</span>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="siswa.php?edit_siswa=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <button onclick="confirmDelete(<?= $row['id'] ?>)" class="btn btn-danger btn-sm">Hapus</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus data siswa ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `siswa.php?delete_siswa=${id}`;
        }
    });
}

// Success notification after deletion
<?php if (isset($_SESSION['message'])): ?>
    Swal.fire({
        title: 'Berhasil!',
        text: '<?= $_SESSION['message'] ?>',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>
</script>
</body>
</html>