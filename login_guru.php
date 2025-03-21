<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Debug: Tampilkan nilai yang diinput
    error_log("Username: " . $username);
    error_log("Password: " . $password);

    // Ubah query untuk debugging
    $query = $conn->prepare("SELECT * FROM login_guru WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Debug: Tampilkan data yang ditemukan
        error_log("Data ditemukan: " . print_r($row, true));
        
        // Cek password tanpa hash dulu untuk debugging
        if ($password === $row['password']) {
            $_SESSION['guru_id'] = $row['id'];
            $_SESSION['guru_nama'] = $row['nama'];
            header("Location: dashboard_guru.php");
            exit();
        } else {
            $error_message = "Password salah!";
            error_log("Password tidak cocok");
        }
    } else {
        $error_message = "Username tidak ditemukan!";
        error_log("Username tidak ditemukan");
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center mb-4">Login Guru</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>
