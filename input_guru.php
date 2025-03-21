<?php
include 'config.php';

$nama = $_POST['nama'] ?? '';
$nip = $_POST['nip'] ?? '';
$mata_pelajaran = $_POST['mata_pelajaran'] ?? '';
$no_hp = $_POST['no_hp'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $today = date("Y-m-d");
    if ($tanggal_lahir >= $today) {
        $_SESSION['error'] = "Tanggal lahir tidak valid. Harus lebih kecil dari hari ini.";
        $_SESSION['input_data'] = $_POST;
        header("Location: input_guru.php");
        exit();
    }

    $cek_nip = $conn->prepare("SELECT nip FROM guru WHERE nip = ?");
    $cek_nip->bind_param("s", $nip);
    $cek_nip->execute();
    $cek_nip->store_result();

    if ($cek_nip->num_rows > 0) {
        $_SESSION['error'] = "NIP sudah terdaftar. Gunakan NIP lain.";
        $_SESSION['input_data'] = $_POST;
        header("Location: input_guru.php");
        exit();
    }

    $foto = $_FILES['foto']['name'] ?? null;
    $target_file = null;

    if ($foto) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($foto);
        move_uploaded_file($_FILES['foto']['tmp_name'], $target_file);
    }

    $sql = "INSERT INTO guru (nama, nip, mata_pelajaran, no_hp, tanggal_lahir, jenis_kelamin, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $nama, $nip, $mata_pelajaran, $no_hp, $tanggal_lahir, $jenis_kelamin, $target_file);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registrasi guru berhasil!";
        unset($_SESSION['input_data']);
    } else {
        $_SESSION['error'] = "Terjadi kesalahan saat menyimpan data.";
    }

    header("Location: input_guru.php");
    exit();
}

$mata_pelajaran_result = $conn->query("SELECT id, nama FROM mata_pelajaran");
$input_data = $_SESSION['input_data'] ?? [];
unset($_SESSION['input_data']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .container-custom { height: 100vh; display: flex; align-items: center; }
        .right-content { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); max-width: 500px; margin: auto; }
        .form-control, .form-select { font-size: 14px; }
        h3 { font-size: 18px; }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let now = new Date();
            let hour = now.getHours();
            let greeting = hour < 11 ? "Halo, Selamat Pagi" : hour < 14 ? "Halo, Selamat Siang" : hour < 19 ? "Halo, Selamat Sore" : "Halo, Selamat Malam";
            document.getElementById("greeting").innerHTML = greeting + " Bapak dan Ibu Guru SMK Negeri 40 Jakarta <br><span style='font-weight: normal; font-size: 14px;'>Selamat datang di Platform input data guru!</span>";
        });
    </script>
</head>
<body class="bg-light">
    <div class="container container-custom">
        <div class="row w-100 justify-content-center">
            <div class="col-md-6 d-flex align-items-center">
                <div class="left-content">
                    <h2 id="greeting" class="fw-bold"></h2>
                </div>
            </div>
            <div class="col-md-6">
                <div class="right-content">
                    <h3 class="text-center mb-3">Registrasi Guru</h3>

                    <!-- Tampilkan Pesan Notifikasi -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php elseif (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-2">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?= $input_data['nama'] ?? '' ?>" required>
                        </div>
                        <div class="mb-2">
                            <label for="nip" class="form-label">NIP</label>
                            <input type="text" class="form-control" id="nip" name="nip" value="<?= $input_data['nip'] ?? '' ?>" required>
                        </div>
                        <div class="mb-2">
                            <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                            <select class="form-select" id="mata_pelajaran" name="mata_pelajaran" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <?php while ($row = $mata_pelajaran_result->fetch_assoc()) { ?>
                                    <option  <?= (isset($input_data['mata_pelajaran']) && $input_data['mata_pelajaran'] == $row['id']) ? 'selected' : ''; ?>>
                                        <?= $row['nama']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label for="no_hp" class="form-label">No HP</label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?= $input_data['no_hp'] ?? '' ?>" required>
                        </div>
                        <div class="mb-2">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?= $input_data['tanggal_lahir'] ?? '' ?>" required>
                        </div>
                        <div class="mb-2">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki" <?= (isset($input_data['jenis_kelamin']) && $input_data['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?= (isset($input_data['jenis_kelamin']) && $input_data['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label for="foto" class="form-label">Foto</label>
                            <input type="file" class="form-control" id="foto" name="foto">
                        </div>
                        <div class="mb-2">
                            <button type="submit" class="btn btn-primary w-100">Daftar</button>
                        </div>
                        <div class="mb-2">
                            <a href="dashboard_admin.php" class="btn btn-secondary w-100">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
