<?php
include 'config.php';

// Check if guru is logged in
if (!isset($_SESSION['guru_login']) || $_SESSION['guru_login'] !== true) {
    header("Location: login.php");
    exit();
}

// Get guru data
$guru_id = $_SESSION['guru_id'];
$query = $conn->prepare("SELECT * FROM guru WHERE id_guru = ?");
$query->bind_param("i", $guru_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $guru_data = $result->fetch_assoc();
} else {
    $guru_data = ['nama' => 'Guru'];
}

// Get current date and time
date_default_timezone_set('Asia/Jakarta');
$current_time = date('H:i');
$current_date = date('l, d F Y');
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden; /* Prevent horizontal scrollbar during animation */
        }
        
        .sidebar {
            width: 250px;
            background-color: #343a40;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 1000;
        }

        .sidebar.closed {
            transform: translateX(-250px);
        }

        .content {
            margin-left: 0; /* Changed from 250px */
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 20px;
            width: 100%;
        }

        .content.shrink {
            margin-left: 250px; /* Shrink content when sidebar is visible */
        }

        .sidebar-logo {
            width: 60px;
            height: auto;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link {
            color: rgba(255,255,255,.8);
            border-radius: 5px;
            margin: 2px 0;
            padding: 10px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background-color: #0d6efd;
            z-index: -1;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 5px;
        }

        .nav-link:hover {
            color: #fff;
            transform: translateX(5px);
        }

        .nav-link:hover::before {
            width: 100%;
        }

        .nav-link.active {
            color: #fff;
            background-color: #0d6efd;
            box-shadow: 0 2px 5px rgba(13, 110, 253, 0.2);
        }

        .nav-link.active::before {
            width: 100%;
        }

        .nav-link i {
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease;
        }

        .nav-link span {
            position: relative;
            z-index: 1;
        }

        .nav-link:hover i {
            transform: scale(1.1);
        }

        .sidebar.closed .menu-text {
            opacity: 0;
            transform: translateX(-20px);
            width: 0;
        }

        .sidebar.closed {
            width: 60px;
        }

        .sidebar.closed .menu-text {
            opacity: 0;
            transform: translateX(-10px);
            display: none;
        }

        .sidebar.closed .sidebar-logo {
            width: 40px;
            transform: scale(0.8);
        }

        .content.expanded {
            margin-left: 60px;
        }

        /* Smooth hover effects */
        .nav-link:hover {
            color: #fff;
            background-color: rgba(255,255,255,.1);
            transform: translateX(5px);
        }

        .nav-link.active {
            color: #fff;
            background-color: #0d6efd;
            box-shadow: 0 2px 5px rgba(13, 110, 253, 0.2);
        }

        /* Smooth card animations */
        .card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
        }

        /* Clock transition */
        .clock {
            background-color: rgba(0,0,0,.2);
            border-radius: 5px;
            font-size: 1.5rem;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 15px 10px;
        }

        .sidebar.closed .clock {
            padding: 10px 5px !important;
            transform: scale(0.9);
            font-size: 1rem; /* Adjusted size for collapsed state */
            font-weight: 700; /* Maintained bold text in collapsed state */
        }

        #time {
            display: block;
            letter-spacing: 1px;
            font-weight: 700;
            transition: color 0.3s ease; /* Changed from opacity */
        }

        .sidebar-title {
            white-space: nowrap;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 1;
            transform: translateX(0);
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .sidebar.closed .sidebar-title {
            opacity: 0;
            transform: translateX(-20px);
            width: 0;
        }

        /* Add a toggle button that's always visible */
        .sidebar-toggle {
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 1001;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: #343a40;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
        }

        .sidebar-toggle.active {
            left: 270px;
        }

        @media (max-width: 768px) {
            .content.shrink {
                margin-left: 0;
            }
            .card-container {
                padding: 0 10px; /* Smaller padding on mobile */
            }
        }

        /* Card styles */
        .card-container {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 0 15px; /* Added padding instead of margin */
        }

        .card-col {
            flex: 1;
            min-width: 300px;
            max-width: 400px; /* Added max-width */
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .container-fluid {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .container-fluid.full-width {
            margin-left: 0;
        }

        .toggle-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background-color: #343a40;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .toggle-btn:hover {
            background-color: #495057;
        }

        /* Add spacing for welcome text */
        .welcome-section {
            margin-left: 50px; /* Add space from toggle button */
            padding-top: 10px;
            margin-right: 120px; /* Add space for logout button */
        }

        /* Specific styles for Total Kehadiran card */
        .attendance-card {
            height: 100%;
            min-height: 200px; /* Set minimum height */
            display: flex;
            flex-direction: column;
        }

        .attendance-card .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5rem; /* Increased padding */
        }

        .attendance-stats {
            display: flex;
            align-items: center;
            margin-top: auto;
            padding: 1rem 0;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(13, 110, 253, 0.1);
            border-radius: 12px;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            transition: all 0.3s ease;
            padding: 8px 20px;
        }

        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
        }

        /* Remove the existing logout link from sidebar */
        .sidebar .nav-link.text-danger {
            display: none;
        }

        .logout-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
        }

        /* Add these styles in the <style> section */
        .development-alert {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.2);
            border-left: 5px solid #ffc107;
        }

        .development-icon {
            font-size: 2rem;
            color: #ffc107;
            animation: spin 4s linear infinite;
        }

        .alert-heading {
            color: #856404;
            font-size: 1.25rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .development-alert .btn-outline-dark {
            transition: all 0.3s ease;
            border-color: #6c757d;
        }

        .development-alert .btn-outline-dark:hover {
            background-color: #6c757d;
            color: white;
            transform: translateY(-2px);
        }

        .development-alert hr {
            border-top-color: rgba(0,0,0,0.1);
            margin: 1rem 0;
        }

        /* Add animation for alert appearance */
        .alert.fade.show {
            animation: slideDown 0.5s ease forwards;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Add these styles in the <style> section of dashboard_guru.php */
        .development-popup-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            z-index: 1050;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .development-popup-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        .development-popup {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            transform: scale(0.7);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .development-popup-backdrop.show .development-popup {
            transform: scale(1);
            opacity: 1;
        }

        .development-icon {
            width: 70px;
            height: 70px;
            background: rgba(255, 193, 7, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .development-icon i {
            font-size: 2rem;
            color: #ffc107;
            animation: spin 4s linear infinite;
        }

        .development-content {
            text-align: center;
        }

        .development-actions {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }

        .development-actions .btn-danger {
            padding: 0.8rem 2rem;
            font-size: 1rem;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .development-actions .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }

        .development-actions {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .btn-continue {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-continue:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Add the toggle button at the top of the body -->
    <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-3 d-flex flex-column vh-100">
            <!-- Header Section -->
            <div class="sidebar-header mb-4">
                <div class="d-flex align-items-center mb-3">
                    <button class="btn btn-link text-white p-0 border-0" onclick="toggleSidebar()">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <span class="ms-3 text-white fs-5 sidebar-title">Panel Guru</span>
                </div>
                <div class="text-center">
                    <img src="Logo 40.png" alt="Logo" class="sidebar-logo mb-2">
                </div>
            </div>

            <!-- Navigation Menu -->
            <div class="nav flex-column mb-auto">
                <a href="dashboard_guru.php" class="nav-link active">
                    <i class="bi bi-house-door"></i> 
                    <span class="menu-text ms-2">Dashboard</span>
                </a>
                <a href="absensi_guru.php" class="nav-link">
                    <i class="bi bi-calendar-check"></i> 
                    <span class="menu-text ms-2">Absensi</span>
                </a>
                <a href="jadwal_guru.php" class="nav-link">
                    <i class="bi bi-calendar3"></i> 
                    <span class="menu-text ms-2">Jadwal Mengajar</span>
                </a>
                <a href="profil_guru.php" class="nav-link">
                    <i class="bi bi-person"></i> 
                    <span class="menu-text ms-2">Profil</span>
                </a>
            </div>

            <!-- Bottom Section -->
            <div class="mt-auto">
                <div id="clock" class="clock text-white p-2 text-center mb-3">
                    <span id="time" class="menu-text"></span>
                </div>
                <a href="logout.php" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right"></i> 
                    <span class="menu-text ms-2">Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content w-100 p-4">
            <div class="container-fluid">
                <!-- Welcome Section -->
                <div class="welcome-section d-flex justify-content-between align-items-center mb-4">
                    <h4>Selamat Datang, <?= isset($guru_data['nama']) ? htmlspecialchars($guru_data['nama']) : 'Guru' ?></h4>
                    <div class="d-flex align-items-center">
                        <a href="logout.php" class="btn btn-danger me-3">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            Logout
                        </a>
                    </div>
                </div>

                <!-- Development Notice Alert -->
                <div class="development-popup-backdrop show">
                    <div class="development-popup">
                        <div class="development-icon">
                            <i class="bi bi-gear-wide-connected"></i>
                        </div>
                        <div class="development-content">
                            <h4 class="alert-heading mb-3">Halaman Dalam Pengembangan</h4>
                            <p class="mb-4">Mohon maaf, dashboard guru masih dalam proses pengembangan. Beberapa fitur belum dapat diakses untuk saat ini.</p>
                            <hr>
                            <p class="mb-4">Silakan kembali ke halaman login.</p>
                        </div>
                        <div class="development-actions">
                            <a href="logout.php" class="btn btn-danger">
                                <i class="bi bi-box-arrow-left me-2"></i>Kembali ke Login
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cards Container -->
                <div class="card-container">
                    <!-- Informasi Guru Card -->
                    <div class="card-col">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="bi bi-person-badge me-2"></i>
                                    Informasi Guru
                                </h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="140">NIP</td>
                                        <td>: <?= htmlspecialchars($guru_data['nip'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td>Nama</td>
                                        <td>: <?= htmlspecialchars($guru_data['nama'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td>Mata Pelajaran</td>
                                        <td>: <?= htmlspecialchars($guru_data['mata_pelajaran'] ?? '-') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Total Kehadiran Card -->
                    <div class="card-col">
                        <div class="card border-0 shadow-sm attendance-card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="bi bi-calendar-check me-2"></i>
                                    Total Kehadiran
                                </h5>
                                <div class="attendance-stats">
                                    <div class="stats-icon">
                                        <i class="bi bi-calendar-check text-primary fs-3"></i>
                                    </div>
                                    <div class="ms-4">
                                        <h6 class="mb-2 text-muted">Persentase Kehadiran</h6>
                                        <h3 class="mb-0 fw-bold">85%</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const container = document.querySelector('.container-fluid');
            const menuTexts = document.querySelectorAll('.menu-text');
            
            sidebar.classList.toggle('closed');
            container.classList.toggle('full-width');
            
            // Add transition delay for menu texts
            menuTexts.forEach((text, index) => {
                text.style.transitionDelay = sidebar.classList.contains('closed') ? '0s' : `${index * 0.05}s`;
            });
        }

        // Update clock with smooth transition
        function updateClock() {
            const now = new Date();
            const options = { 
                timeZone: 'Asia/Jakarta', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            };
            const timeString = now.toLocaleTimeString('id-ID', options);
            const timeElement = document.getElementById('time');
            
            // Remove fade effect, just update text
            timeElement.textContent = timeString + ' WIB';
        }

        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>