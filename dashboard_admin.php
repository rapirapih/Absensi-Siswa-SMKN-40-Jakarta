<?php
include 'config.php';

// Add this function at the top of the file after include 'config.php'
function checkAndResetAbsensi() {
    global $conn;
    date_default_timezone_set('Asia/Jakarta');
    $current_time = date('H:i');
    $current_date = date('Y-m-d');
    
    // Check if it's 17:00
    if ($current_time >= '17:00' && $current_time <= '17:05') {
        // Check if data has been backed up today
        $backup_check = $conn->query("SELECT * FROM absensi_backup WHERE tanggal = CURDATE()");
        
        if ($backup_check->num_rows == 0) {
            // Create backup of today's attendance
            $backup_query = "INSERT INTO absensi_backup (siswa_id, tanggal, status, waktu)
                           SELECT siswa_id, tanggal, status, waktu 
                           FROM absensi 
                           WHERE DATE(tanggal) = CURDATE()";
            
            if ($conn->query($backup_query)) {
                // Reset the absensi table
                $reset_query = "DELETE FROM absensi WHERE DATE(tanggal) = CURDATE()";
                if ($conn->query($reset_query)) {
                    return [
                        'should_notify' => true,
                        'message' => 'Data absensi hari ini telah dibackup dan akan direset dalam beberapa menit.'
                    ];
                }
            }
        }
    }
    return ['should_notify' => false];
}

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta'); 
// Update the query at the top of the file
$query = "SELECT 
            a.id,
            a.tanggal,
            a.status, 
            s.nama, 
            s.nis, 
            s.kelas, 
            s.jurusan,
            s.gambar, /* Add this line to get student photo */
            CASE 
                WHEN a.status = 'Hadir' THEN 'bg-success'
                WHEN a.status = 'Sakit' THEN 'bg-warning'  /* Changed from badge-sakit */
                WHEN a.status = 'Izin' THEN 'bg-danger'   /* Changed from badge-info */
                ELSE 'bg-secondary'
            END AS status_class
          FROM absensi a
          JOIN siswa s ON a.siswa_id = s.id 
          WHERE DATE(a.tanggal) = CURDATE()
          ORDER BY a.tanggal DESC";

$absensi_result = $conn->query($query);
if (!$absensi_result) {
    die("Error pada query data absensi: " . $conn->error);
}

// Debugging: Log jika tidak ada data
if ($absensi_result->num_rows === 0) {
    error_log("Tidak ada data absensi untuk hari ini");
}

// Query untuk menghitung statistik absensi hari ini
$stats_query = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) AS hadir,
                    SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) AS sakit,
                    SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) AS izin
                FROM absensi 
                WHERE DATE(tanggal) = CURDATE()";
$stats_result = $conn->query($stats_query);
if (!$stats_result) {
    die("Error pada query statistik: " . $conn->error);
}
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-bg: #1a237e;
      --secondary-bg: #283593;
      --accent-color: #3949ab;
      --text-light: #ffffff;
      --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
      --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
      --transition: all 0.3s ease;
      --breakpoint-sm: 576px;
      --breakpoint-md: 768px;
      --breakpoint-lg: 992px;
      --breakpoint-xl: 1200px;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      overflow-x: hidden;
    }

    /* Modern Sidebar */
    .sidebar {
      background: linear-gradient(165deg, var(--primary-bg), var(--secondary-bg));
      width: 80px; /* Reduced initial width */
      height: 100vh; /* Keep full height */
      padding: 1rem;
      position: fixed;
      left: 0;
      top: 0;
      z-index: 1000;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.15);
      transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar:hover {
      width: 250px; /* Expand to full width on hover */
    }

    /* Adjust nav items for vertical layout */
    .nav.flex-column {
      opacity: 0;
      transform: translateY(-20px);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      visibility: hidden;
    }

    .sidebar:hover .nav.flex-column {
      opacity: 1;
      transform: translateY(0);
      visibility: visible;
      margin-top: 1rem;
    }

    /* Adjust header for collapsed state */
    .sidebar-header {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 2rem;
      position: relative;
    }

    .sidebar:hover .sidebar-header {
      flex-direction: column;
      text-align: center;
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .sidebar-logo {
      width: 45px;
      height: 45px;
      border-radius: 12px;
      padding: 8px;
      background: rgba(255,255,255,0.1);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      margin: 0;
    }

    .sidebar:hover .sidebar-logo {
      margin-bottom: 0.5rem;
    }

    .menu-text {
      position: absolute;
      white-space: nowrap;
      opacity: 0;
      transform: translateX(10px);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      visibility: hidden;
    }

    .sidebar:hover .menu-text {
      opacity: 1;
      transform: translateX(0);
      visibility: visible;
      position: relative;
    }

    /* Adjust content margin */
    .content {
      margin-left: 80px; /* Match sidebar width */
      padding: 2rem;
      width: calc(100% - 80px); /* Full width minus sidebar */
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar:hover ~ .content {
      margin-left: 250px; /* Match expanded sidebar width */
      width: calc(100% - 250px);
    }

    /* Update responsive styles */
    @media (max-width: 768px) {
      .sidebar {
        height: 70px;
        width: 100%;
      }
      
      .sidebar.active {
        height: 100vh;
      }
      
      .content {
        margin-left: 0;
        margin-top: 70px;
        width: 100%;
      }
      
      .sidebar-header {
        justify-content: center;
      }
    }

    .nav-link {
      color: rgba(255,255,255,0.8);
      padding: 0.8rem;
      margin: 0.3rem 0;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar:hover .nav-link {
      justify-content: flex-start;
      padding: 0.8rem 1rem;
    }

    .nav-link::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      width: 0;
      height: 100%;
      background: rgba(255,255,255,0.1);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 12px;
    }

    .nav-link:hover::before {
      width: 100%;
    }

    .nav-link i {
      font-size: 1.3rem;
      min-width: 35px;
      height: 35px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      margin-right: 0;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      background: rgba(255,255,255,0.1);
    }

    .sidebar:hover .nav-link i {
      margin-right: 1rem;
    }

    .nav-link:hover i {
      transform: scale(1.1) rotate(10deg);
      background: rgba(255,255,255,0.2);
    }

    .nav-link .menu-text {
      opacity: 0;
      transform: translateX(-10px);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      font-weight: 500;
      font-size: 0.95rem;
      white-space: nowrap;
    }

    .sidebar:hover .nav-link .menu-text {
      opacity: 1;
      transform: translateX(0);
    }

    .nav-link:hover {
      color: #fff;
      transform: translateX(5px);
    }

    .nav-link.active {
      background: rgba(255,255,255,0.2);
      color: #fff;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .nav-link.active i {
      background: var(--accent-color);
      color: #fff;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    /* Add animation for menu items */
    .nav-item {
      opacity: 0;
      transform: translateX(-20px);
      animation: slideIn 0.5s ease forwards;
    }

    @keyframes slideIn {
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .nav-item:nth-child(1) { animation-delay: 0.2s; }
    .nav-item:nth-child(2) { animation-delay: 0.3s; }
    .nav-item:nth-child(3) { animation-delay: 0.4s; }
    .nav-item:nth-child(4) { animation-delay: 0.5s; }

    /* Adjust main content */
    .content {
      margin-left: 80px; /* Match sidebar width */
      padding: 2rem;
      transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
      width: calc(100% - 80px); /* Full width minus sidebar */
    }

    .sidebar:hover ~ .content {
      margin-left: 260px; /* Match expanded sidebar width */
      width: calc(100% - 260px);
    }

    /* Active state for nav links */
    .nav-link.active {
      background: var(--accent-color);
      color: var(--text-light);
    }

    .nav-link:hover {
      background: rgba(255,255,255,0.1);
      transform: translateX(5px);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
        width: 250px;
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
      
      .content {
        margin-left: 0;
        width: 100%;
      }
      
      .content.shifted {
        margin-left: 250px;
        width: calc(100% - 250px);
      }
    }

    /* Modern Content Area */
    .main-header {
      background: white;
      padding: 1rem;
      border-radius: 12px;
      box-shadow: var(--shadow-sm);
      margin-bottom: 1rem;
      flex-wrap: wrap;
    }

    .d-flex.align-items-center.gap-3 {
      display: flex;
      align-items: center; /* Change back to center */
      gap: 1rem;
    }

    /* Modern Stats Cards */
    .stats-card {
      border-radius: 25px;
      border: none;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }

    .stats-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(45deg, rgba(255,255,255,0.15), rgba(255,255,255,0));
      z-index: 1;
    }

    .stats-card .card-body {
      padding: 2rem;
      position: relative;
      z-index: 2;
      background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
      border-radius: 24px;
      transition: transform 0.3s ease;
    }

    .stats-card .icon-wrapper {
      width: 65px;
      height: 65px;
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1.5rem;
      position: relative;
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(5px);
    }

    .stats-card .icon-wrapper i {
      font-size: 2rem;
      color: #fff;
      transition: all 0.5s ease;
    }

    .stats-card .stats-info {
      position: relative;
    }

    .stats-card h6 {
      font-size: 1.1rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 1rem;
      color: rgba(255,255,255,0.9);
    }

    .stats-card h3 {
      font-size: 3rem;
      font-weight: 700;
      margin: 0;
      line-height: 1;
      margin-bottom: 0.5rem;
      background: linear-gradient(45deg, #fff, rgba(255,255,255,0.8));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .stats-card small {
      font-size: 0.9rem;
      color: rgba(255,255,255,0.7);
      letter-spacing: 0.5px;
    }

    /* Card specific gradients */
    .stats-card.bg-success {
      background: linear-gradient(135deg, #28a745, #20c997);
    }

    .stats-card.bg-warning {
      background: linear-gradient(135deg, #ff9f43, #ffc107);
    }

    .stats-card.bg-danger {
      background: linear-gradient(135deg, #ee5253, #dc3545);
    }

    .stats-card.bg-primary {
      background: linear-gradient(135deg, #4481eb, #0d6efd);
    }

    /* Hover effects */
    .stats-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }

    .stats-card:hover .card-body {
      transform: scale(1.02);
    }

    .stats-card:hover .icon-wrapper {
      transform: scale(1.1) rotate(10deg);
      background: rgba(255,255,255,0.2);
    }

    .stats-card:hover .icon-wrapper i {
      transform: rotate(360deg);
    }

    /* Animation keyframes */
    @keyframes statsFloat {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }

    @keyframes numberGlow {
      0%, 100% { text-shadow: 0 0 15px rgba(255,255,255,0.3); }
      50% { text-shadow: 0 0 25px rgba(255,255,255,0.5); }
    }

    /* Apply animations */
    .stats-card h3 {
      animation: numberGlow 2s infinite;
    }

    .stats-card {
      animation: statsFloat 6s ease-in-out infinite;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .stats-card {
        margin-bottom: 1rem;
      }
      
      .stats-card h3 {
        font-size: 2.5rem;
      }
      
      .stats-card .icon-wrapper {
        width: 55px;
        height: 55px;
      }
      
      .stats-card .icon-wrapper i {
        font-size: 1.7rem;
      }
    }

    /* Modern Table Container */
    .table-container {
      background: white;
      border-radius: 12px;
      box-shadow: var(--shadow-sm);
      margin-top: 2rem;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      margin: 1rem -1rem;
      padding: 0 1rem;
    }

    .table {
      margin: 0;
      min-width: 800px; /* Minimum width for horizontal scroll */
    }

    .table thead th {
      background: var(--primary-bg);
      color: var(--text-light);
      font-weight: 500;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
    }

    .badge {
      padding: 0.5em 1em;
      font-weight: 500;
      letter-spacing: 0.5px;
    }

    /* Modern Clock */
    #clock {
      background: var(--secondary-bg);
      padding: 1rem;
      border-radius: 8px;
      font-size: 1.1rem;
      font-weight: 500;
      color: var(--text-light);
      text-align: center;
      margin-top: 2rem;
    }

    /* Modern Buttons */
    .btn-danger {
      height: 38px;  /* Match clock height */
      padding: 0.375rem 0.75rem;  /* Bootstrap default padding */
      font-size: 0.9rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      line-height: 1.5;
      background: #dc3545;
      border: none;
      border-radius: 8px;
      transition: var(--transition);
      transform: translateY(0); /* Remove the upward transform */
      width: 135px; /* Fixed width to match clock */
      justify-content: center;
    }

    .btn-danger i {
      margin-right: 0.5rem;
      font-size: 1rem;
    }

    .btn-danger:hover {
      background: #c0392b;
      transform: translateY(-2px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .sidebar.active {
        transform: translateX(0);
      }

      .content {
        margin-left: 0;
      }
    }

    /* Add this for sidebar toggle functionality */
    .sidebar-toggle {
      position: fixed;
      left: 20px;
      top: 20px;
      z-index: 1001;
      background: var(--primary-bg);
      border: none;
      color: var (--text-light);
      padding: 10px;
      border-radius: 8px;
      cursor: pointer;
      display: none;
    }

    @media (max-width: 768px) {
      .sidebar-toggle {
        display: block;
      }
    }

    /* Add gap utility class */
    .gap-3 {
      gap: 1rem !important;
    }

    .gap-4 {
      gap: 1.5rem !important;
    }

    /* Update Container Padding */
    .p-3 {
      padding: 1.5rem !important;
    }

    /* Update Stats Cards Layout */
    .row.g-3 {
      margin: 0 -0.75rem 2rem;
    }

    .col-md-3 {
      padding: 0 0.75rem;
    }

    /* Update the clock and container styles in dashboard_admin.php */
    .header-controls {
      display: flex;
      flex-direction: row; /* Change from column to row */
      gap: 0.5rem; /* Reduce gap between elements */
      align-items: center; /* Center items vertically */
    }

    /* Update button and clock styles to be consistent */
    .btn-danger, #clock {
      height: 38px;
      padding: 0.375rem 0.75rem;
      font-size: 0.9rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      line-height: 1.5;
      border-radius: 8px;
      transition: var (--transition);
      width: auto; /* Remove fixed width */
      min-width: 100px; /* Add minimum width instead */
      justify-content: center;
    }

    /* Update the main header layout */
    .main-header {
      background: white;
      padding: 1rem;
      border-radius: 12px;
      box-shadow: var(--shadow-sm);
      margin-bottom: 1rem;
    }

    .d-flex.align-items-center.gap-4 {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }

    /* Responsive Grid System */
    .row.g-3 {
      margin: 0 -0.5rem 1rem;
    }

    @media (max-width: 576px) {
      .col-md-3 {
        padding: 0 0.5rem;
        margin-bottom: 1rem;
      }
      
      .stats-card {
        height: 100%;
        min-height: auto;
      }
      
      .stats-card .card-body {
        padding: 1rem;
      }
      
      .stats-card h3 {
        font-size: 1.5rem;
      }
    }

    /* Responsive Table */
    .table-container {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      margin: 1rem -1rem;
      padding: 0 1rem;
    }

    .table {
      min-width: 800px; /* Minimum width for horizontal scroll */
    }

    @media (max-width: 992px) {
      .table-container {
        padding: 1rem;
      }
      
      .table thead th {
        white-space: nowrap;
      }
    }

    /* Responsive Header */
    .main-header {
      padding: 1rem;
      margin: 0 0 1rem 0;
      flex-wrap: wrap;
    }

    @media (max-width: 768px) {
      .main-header {
        padding: 1rem;
      }

      .main-header h4 {
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
      }
      
      .header-controls {
        width: 100%;
        justify-content: flex-end;
        margin-top: 0.5rem;
      }
      
      .btn-danger {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
      }
    }

    /* Responsive Sidebar */
    @media (max-width: 768px) {
      .sidebar {
        width: 0;
        padding: 1rem 0;
      }
      
      .sidebar.active {
        width: 250px;
        padding: 1rem;
      }
      
      .content {
        margin-left: 0;
        padding: 1rem;
      }
      
      .sidebar-toggle {
        display: block;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1060;
      }
    }

    /* Fix container paddings */
    .container-fluid {
      padding-right: 0;
      padding-left: 0;
    }

    @media (min-width: 768px) {
      .container-fluid {
        padding-right: 1rem;
        padding-left: 1rem;
      }
    }

    /* Stats Cards Grid */
    @media (max-width: 768px) {
      .row.g-3 > .col-md-3 {
        width: 50%;
      }
    }

    @media (max-width: 576px) {
      .row.g-3 > .col-md-3 {
        width: 100%;
      }
    }

    /* Container fluid updates */
    .container-fluid {
      padding: 0;
      margin: 0;
      width: 100%;
    }

    /* Make main content sticky */
    main.content {
      min-height: 100vh;
      position: relative;
      background: #f8f9fa;
    }

    /* Add these styles to your existing CSS */
    .filter-section {
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
        padding: 1.5rem;
    }

    .form-select {
        border-radius: 8px;
        border: 1px solid rgba(0,0,0,0.1);
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .form-select:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 0.2rem rgba(57, 73, 171, 0.25);
    }

    .btn-primary {
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .no-data-message {
        margin-top: 1rem;
        text-align: center;
        border-radius: 8px;
        padding: 1rem;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Add this CSS for the filter button container */
    .filter-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0,0,0,0.1);
    }

    /* Add these styles in your CSS section */
    .modal-foto {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        z-index: 1100;
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(8px);
    }

    .modal-foto.active {
        display: flex;
        opacity: 1;
        justify-content: center;
        align-items: center;
    }

    .modal-content-foto {
        background: linear-gradient(135deg, #ffffff, #f8f9fa);
        padding: 2rem;
        border-radius: 20px;
        max-width: 500px;
        width: 90%;
        transform: scale(0.7) translateY(-50px);
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-align: center;
        position: relative;
        box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        overflow: hidden;
    }

    .modal-foto.active .modal-content-foto {
        transform: scale(1) translateY(0);
    }

    .student-photo-wrapper {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto 1.5rem;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        transition: transform 0.3s ease;
    }

    .student-photo-wrapper:hover {
        transform: scale(1.05);
    }

    .student-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 15px;
    }

    .student-info {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 15px;
        padding: 1.5rem;
        margin-top: 1.5rem;
        text-align: left;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .student-info h5 {
        color: var(--primary-bg);
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        text-align: center;
        font-weight: 600;
    }

    .info-table {
        width: 100%;
        margin-bottom: 0;
    }

    .info-table td {
        padding: 0.75rem 0;
        border: none;
        font-size: 0.95rem;
    }

    .info-table td:first-child {
        color: #666;
        font-weight: 500;
        width: 120px;
    }

    .info-table td:last-child {
        color: #333;
        font-weight: 600;
    }

    .status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    .close-modal {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(255,255,255,0.9);
        border: none;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .close-modal:hover {
        transform: scale(1.1) rotate(90deg);
        background: #fff;
    }

    .close-modal i {
        font-size: 1.2rem;
        color: #666;
    }

    /* Add animation for modal content */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .student-info > * {
        animation: slideIn 0.5s ease forwards;
    }

    .student-info > *:nth-child(1) { animation-delay: 0.1s; }
    .student-info > *:nth-child(2) { animation-delay: 0.2s; }
    .student-info > *:nth-child(3) { animation-delay: 0.3s; }
    .student-info > *:nth-child(4) { animation-delay: 0.4s; }

    /* Add these styles in your CSS section */
.loading-animation {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1200;
    backdrop-filter: blur(8px);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.loading-animation.show {
    opacity: 1;
    visibility: visible;
}

.loader {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 5px solid rgba(255,255,255,0.1);
    border-top-color: var(--primary-bg);
    animation: spin 1s infinite ease-in-out;
    transform: scale(0);
    transition: transform 0.3s ease;
}

.loading-animation.show .loader {
    transform: scale(1);
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-text {
    position: absolute;
    bottom: calc(50% - 60px);
    color: white;
    font-size: 0.9rem;
    font-weight: 500;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease 0.2s;
}

.loading-animation.show .loading-text {
    opacity: 1;
    transform: translateY(0);
}

/* Update/add these styles in dashboard_admin.php */

/* Loading Animation Styles */
.loading-animation {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1200;
    opacity: 0;
    visibility: hidden;
    backdrop-filter: blur(8px);
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.loading-animation.show {
    opacity: 1;
    visibility: visible;
}

.loader {
    width: 60px;
    height: 60px;
    border: 3px solid rgba(255, 255, 255, 0.2);
    border-top-color: var(--primary-bg);
    border-radius: 50%;
    animation: loader-spin 1s ease-in-out infinite;
    transform: scale(0);
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.loading-animation.show .loader {
    transform: scale(1);
}

@keyframes loader-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    position: absolute;
    bottom: calc(50% - 50px);
    color: white;
    font-size: 0.95rem;
    font-weight: 500;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.loading-animation.show .loading-text {
    opacity: 1;
    transform: translateY(0);
}

/* Modal Styles */
.modal-foto {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    z-index: 1100;
    opacity: 0;
    backdrop-filter: blur(8px);
    transition: opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-foto.active {
    display: flex;
    opacity: 1;
    justify-content: center;
    align-items: center;
}

.modal-content-foto {
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    padding: 2rem;
    border-radius: 20px;
    max-width: 500px;
    width: 90%;
    transform: scale(0.7) translateY(-30px);
    transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    opacity: 0;
}

.modal-foto.active .modal-content-foto {
    transform: scale(1) translateY(0);
    opacity: 1;
}

/* Student Info Animation */
.student-info > * {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-foto.active .student-info > * {
    opacity: 1;
    transform: translateY(0);
}

.modal-foto.active .student-info > *:nth-child(1) { transition-delay: 0.2s; }
.modal-foto.active .student-info > *:nth-child(2) { transition-delay: 0.3s; }
.modal-foto.active .student-info > *:nth-child(3) { transition-delay: 0.4s; }
.modal-foto.active .student-info > *:nth-child(4) { transition-delay: 0.5s; }

/* Photo Wrapper Animation */
.student-photo-wrapper {
    transform: scale(0.9);
    opacity: 0;
    transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-foto.active .student-photo-wrapper {
    transform: scale(1);
    opacity: 1;
    transition-delay: 0.1s;
}

/* Add these styles to your existing CSS */
.no-photo-message {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 15px;
    color: #6c757d;
}

.no-photo-message i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.no-photo-message p {
    font-size: 1rem;
    font-weight: 500;
    margin: 0;
}

.student-photo-wrapper {
    position: relative;
    width: 200px;
    height: 200px;
    margin: 0 auto 1.5rem;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    transition: transform 0.3s ease;
    background: #f8f9fa;
}

/* Enhanced Stats Cards Styling */
.stats-card {
    border-radius: 25px;
    border: none;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(255,255,255,0.15), rgba(255,255,255,0));
    z-index: 1;
}

.stats-card .card-body {
    padding: 2rem;
    position: relative;
    z-index: 2;
    background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
    border-radius: 24px;
    transition: transform 0.3s ease;
}

.stats-card .icon-wrapper {
    width: 65px;
    height: 65px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    position: relative;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(5px);
}

.stats-card .icon-wrapper i {
    font-size: 2rem;
    color: #fff;
    transition: all 0.5s ease;
}

.stats-card .stats-info {
    position: relative;
}

.stats-card h6 {
    font-size: 1.1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 1rem;
    color: rgba(255,255,255,0.9);
}

.stats-card h3 {
    font-size: 3rem;
    font-weight: 700;
    margin: 0;
    line-height: 1;
    margin-bottom: 0.5rem;
    background: linear-gradient(45deg, #fff, rgba(255,255,255,0.8));
    -webkit-background-clip: text;  
    -webkit-text-fill-color: transparent;
}

.stats-card small {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.7);
    letter-spacing: 0.5px;
}

/* Card specific gradients */
.stats-card.bg-success {
    background: linear-gradient(135deg, #00b09b, #96c93d);
}

.stats-card.bg-warning {
    background: linear-gradient(135deg, #f7971e, #ffd200);
}

.stats-card.bg-danger {
    background: linear-gradient(135deg, #ff416c, #ff4b2b);
}

.stats-card.bg-primary {
    background: linear-gradient(135deg, #4e54c8, #8f94fb);
}

/* Hover effects */
.stats-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

.stats-card:hover .card-body {
    transform: scale(1.02);
}

.stats-card:hover .icon-wrapper {
    transform: scale(1.1) rotate(10deg);
    background: rgba(255,255,255,0.2);
}

.stats-card:hover .icon-wrapper i {
    transform: rotate(360deg);
}

/* Animation keyframes */
@keyframes statsFloat {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

@keyframes numberGlow {
    0%, 100% { text-shadow: 0 0 15px rgba(255,255,255,0.3); }
    50% { text-shadow: 0 0 25px rgba(255,255,255,0.5); }
}

/* Apply animations */
.stats-card h3 {
    animation: numberGlow 2s infinite;
}

.stats-card {
    animation: statsFloat 6s ease-in-out infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .stats-card h3 {
        font-size: 2.5rem;
    }
    
    .stats-card .icon-wrapper {
        width: 55px;
        height: 55px;
    }
    
    .stats-card .icon-wrapper i {
        font-size: 1.7rem;
    }
}

/* Add these styles to the existing <style> section in login.php */
.city-background {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(0deg, #1a237e 0%, #3949ab 100%);
    z-index: -2;
}

.cityscape {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 200%;
    height: 25%;
    background: url('assets/jakarta-skyline.png') repeat-x;
    background-size: 50% 100%;
    animation: moveCityscape 40s linear infinite;
    opacity: 0.8;
}

.stars {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('assets/stars.png');
    animation: twinkle 2s linear infinite;
}

.clouds {
    position: absolute;
    top: 0;
    width: 200%;
    height: 100%;
    background: url('assets/clouds.png') repeat-x;
    opacity: 0.4;
    animation: moveClouds 30s linear infinite;
}

.clouds:nth-child(2) {
    top: 20%;
    opacity: 0.3;
    animation: moveClouds 45s linear infinite;
}

@keyframes moveCityscape {
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
}

@keyframes moveClouds {
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
}

@keyframes twinkle {
    0% { opacity: 0.5; }
    50% { opacity: 1; }
    100% { opacity: 0.5; }
}

/* Update existing bg-slider for better overlay */
.bg-slider {
    z-index: -1;
    opacity: 0.7;
}
  </style>
</head>
<body>
  <div class="container-fluid p-0">
    <div class="row g-0"> <!-- Remove default gutters -->
      <!-- Sidebar -->
      <nav class="sidebar">
        <div class="sidebar-header">
            <img src="Logo 40.png" alt="Logo" class="sidebar-logo">
            <h6 class="text-white mb-0 menu-text">Panel Admin</h6>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard_admin.php">
                    <i class="bi bi-speedometer2"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="siswa.php">
                    <i class="bi bi-mortarboard"></i>
                    <span class="menu-text">Data Siswa</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="input_guru.php">
                    <i class="bi bi-person-plus"></i>
                    <span class="menu-text">Input Guru</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="data_guru.php">
                    <i class="bi bi-people"></i>
                    <span class="menu-text">Data Guru</span>
                </a>
            </li>
        </ul>
      </nav>

      <!-- Main Content -->
      <main class="content">
        <!-- Header -->
        <header class="main-header">
          <div class="d-flex align-items-center gap-4 w-100">
            <h4 class="mb-0">Dashboard Admin</h4>
            <div class="ms-auto header-controls">
              <a href="logout.php" class="btn btn-danger">
                <i class="bi bi-box-arrow-right me-1"></i>
                Logout
              </a>
            </div>
          </div>
        </header>

        <div class="p-3">
          <!-- Statistik Absensi -->
          <div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <div class="icon-wrapper">
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="stats-info">
                    <h6>Hadir</h6>
                    <h3 class="number-animate"><?= $stats['hadir'] ?? 0 ?></h3>
                    <small class="text-white-50 mt-2">Siswa hadir hari ini</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-warning">
            <div class="card-body">
                <div class="icon-wrapper">
                    <i class="bi bi-thermometer-half"></i>
                </div>
                <div class="stats-info">
                    <h6>Sakit</h6>
                    <h3 class="number-animate"><?= $stats['sakit'] ?? 0 ?></h3>
                    <small class="text-dark-50 mt-2">Siswa sakit hari ini</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-danger text-white">
            <div class="card-body">
                <div class="icon-wrapper">
                    <i class="bi bi-envelope"></i>
                </div>
                <div class="stats-info">
                    <h6>Izin</h6>
                    <h3 class="number-animate"><?= $stats['izin'] ?? 0 ?></h3>
                    <small class="text-white-50 mt-2">Siswa izin hari ini</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <div class="icon-wrapper">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stats-info">
                    <h6>Total</h6>
                    <h3 class="number-animate"><?= $stats['total'] ?? 0 ?></h3>
                    <small class="text-white-50 mt-2">Total kehadiran hari ini</small>
                </div>
            </div>
        </div>
    </div>
</div>

          <!-- Add filter form above the table -->
          <div class="card mb-4">
            <div class="card-body">
              <h6 class="card-title mb-3">Filter Data</h6>
              <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Kelas</label>
                  <select class="form-select" name="kelas" id="filterKelas">
                    <option value="">Semua Kelas</option>
                    <?php
                    $kelas_query = "SELECT DISTINCT kelas FROM siswa ORDER BY kelas";
                    $kelas_result = $conn->query($kelas_query);
                    while ($kelas = $kelas_result->fetch_assoc()) {
                      echo "<option value='" . htmlspecialchars($kelas['kelas']) . "'>" . htmlspecialchars($kelas['kelas']) . "</option>";
                    }
                    ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Jurusan</label>
                  <select class="form-select" name="jurusan" id="filterJurusan">
                    <option value="">Semua Jurusan</option>
                    <?php
                    $jurusan_query = "SELECT DISTINCT jurusan FROM siswa ORDER BY jurusan";
                    $jurusan_result = $conn->query($jurusan_query);
                    while ($jurusan = $jurusan_result->fetch_assoc()) {
                      echo "<option value='" . htmlspecialchars($jurusan['jurusan']) . "'>" . htmlspecialchars($jurusan['jurusan']) . "</option>";
                    }
                    ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Status</label>
                  <select class="form-select" name="status" id="filterStatus">
                    <option value="">Semua Status</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin</option>
                  </select>
                </div>
                <div class="col-12">
                  <div class="filter-buttons">
                    <button type="button" class="btn btn-secondary" id="resetFilter">
                      <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Filter
                    </button>
                    <button type="button" class="btn btn-primary" id="applyFilter">
                      <i class="bi bi-funnel me-2"></i>Terapkan Filter
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- Data Absensi -->
          <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="bi bi-calendar-check me-2"></i>Data Absensi Hari Ini</h5>
                <button onclick="downloadExcel()" class="btn btn-success">
                    <i class="bi bi-download me-2"></i>Download Excel
                </button>
            </div>
            <?php if ($absensi_result->num_rows > 0): ?>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead class="table-dark">
                    <tr>
                      <th>Waktu</th>
                      <th>Nama Siswa</th>
                      <th>NIS</th>
                      <th>Kelas</th>
                      <th>Jurusan</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
    <?php while ($row = $absensi_result->fetch_assoc()): ?>
    <tr style="cursor: pointer;" onclick="showStudentPhoto('<?= htmlspecialchars($row['nama']) ?>', '<?= htmlspecialchars($row['nis']) ?>', '<?= htmlspecialchars($row['kelas']) ?>', '<?= htmlspecialchars($row['jurusan']) ?>', '<?= htmlspecialchars($row['gambar'] ?? 'default.png') ?>', '<?= htmlspecialchars($row['status']) ?>')">
        <td><?= date('H:i', strtotime($row['tanggal'])) ?></td>
        <td><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= htmlspecialchars($row['nis']) ?></td>
        <td><?= htmlspecialchars($row['kelas']) ?></td>
        <td><?= htmlspecialchars($row['jurusan']) ?></td>
        <td>
            <span class="badge <?= htmlspecialchars($row['status_class']) ?>">
                <?= htmlspecialchars($row['status']) ?>
            </span>
        </td>
    </tr>
    <?php endwhile; ?>
</tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="alert alert-info">
                Belum ada data absensi untuk hari ini.
              </div>
            <?php endif; ?>
          </div>
        </div>
        <!-- Add this after your table container -->
<div class="modal-foto" id="photoModal">
    <div class="modal-content-foto">
        <button class="close-modal" onclick="closePhotoModal()">
            <i class="bi bi-x"></i>
        </button>
        
        <div class="student-photo-wrapper">
            <img src="" alt="Foto Siswa" class="student-photo" id="studentPhoto">
        </div>
        
        <div class="student-info">
            <h5 id="studentName"></h5>
            <table class="info-table">
                <tr>
                    <td>NIS</td>
                    <td>: <span id="studentNIS"></span></td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td>: <span id="studentClass"></span></td>
                </tr>
                <tr>
                    <td>Jurusan</td>
                    <td>: <span id="studentMajor"></span></td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>: <span id="studentStatus" class="status-badge"></span></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<!-- Add this before the photo modal -->
<div class="loading-animation" id="loadingAnimation">
    <div class="loader"></div>
    <div class="loading-text">Memuat data siswa...</div>
</div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
        
        sidebar.addEventListener('mouseleave', () => {
            sidebar.style.height = '80px';
        });
        
        sidebar.addEventListener('mouseenter', () => {
            sidebar.style.height = '100vh';
        });
    
    // Mobile toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }
  </script>
  <script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const tableBody = document.querySelector('.table tbody');
    const allRows = [...tableBody.querySelectorAll('tr')];
    
    // Apply Filter
    document.getElementById('applyFilter').addEventListener('click', function() {
        // ... existing filter logic ...
    });
    
    // Reset Filter
    document.getElementById('resetFilter').addEventListener('click', function() {
        filterForm.reset();
        allRows.forEach(row => row.style.display = '');
        const noDataMessage = document.querySelector('.no-data-message');
        if (noDataMessage) noDataMessage.remove();
    });

    // Remove the old reset button creation code
});
</script>
<script>
// Update the showStudentPhoto function in dashboard_admin.php
function showStudentPhoto(name, nis, kelas, jurusan, photo, status) {
    const loading = document.getElementById('loadingAnimation');
    const modal = document.getElementById('photoModal');
    const studentPhoto = document.getElementById('studentPhoto');
    const photoWrapper = document.querySelector('.student-photo-wrapper');
    const statusSpan = document.getElementById('studentStatus');
    
    loading.classList.add('show');
    
    setTimeout(() => {
        // Set student info
        document.getElementById('studentName').textContent = name;
        document.getElementById('studentNIS').textContent = nis;
        document.getElementById('studentClass').textContent = kelas;
        document.getElementById('studentMajor').textContent = jurusan;
        
        // Set status with appropriate color
        statusSpan.textContent = status;
        statusSpan.className = 'status-badge';
        switch(status) {
            case 'Hadir':
                statusSpan.style.backgroundColor = '#28a745';
                statusSpan.style.color = '#fff';
                break;
            case 'Sakit':
                statusSpan.style.backgroundColor = '#ffc107';
                statusSpan.style.color = '#000';
                break;
            case 'Izin':
                statusSpan.style.backgroundColor = '#dc3545';
                statusSpan.style.color = '#fff';
                break;
        }
        
        // Check if photo exists
        if (!photo || photo === 'default.png') {
            photoWrapper.innerHTML = `
                <div class="no-photo-message">
                    <i class="bi bi-person-x text-muted"></i>
                    <p>Foto tidak ada</p>
                </div>
            `;
        } else {
            photoWrapper.innerHTML = `
                <img src="uploads/${photo}" alt="Foto Siswa" class="student-photo" id="studentPhoto">
            `;
            
            const newStudentPhoto = photoWrapper.querySelector('.student-photo');
            newStudentPhoto.onerror = function() {
                photoWrapper.innerHTML = `
                    <div class="no-photo-message">
                        <i class="bi bi-person-x text-muted"></i>
                        <p>Foto tidak ada</p>
                    </div>
                `;
                showModal();
            };
            
            newStudentPhoto.onload = function() {
                showModal();
            };
        }
        
        // Show modal immediately if no photo
        if (!photo || photo === 'default.png') {
            showModal();
        }
    }, 600);
    
    function showModal() {
        setTimeout(() => {
            loading.classList.remove('show');
            setTimeout(() => {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }, 300);
        }, 500);
    }
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    const loading = document.getElementById('loadingAnimation');
    
    modal.classList.remove('active');
    loading.classList.remove('show');
    
    setTimeout(() => {
        document.body.style.overflow = '';
    }, 600);
}

// Close modal when clicking outside
document.getElementById('photoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePhotoModal();
    }
});
</script>
<script>
// Add this after your existing scripts in dashboard_admin.php
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const tableBody = document.querySelector('.table tbody');
    const allRows = [...tableBody.querySelectorAll('tr')];
    
    // Apply Filter function
    document.getElementById('applyFilter').addEventListener('click', function() {
        const kelas = document.getElementById('filterKelas').value.toLowerCase();
        const jurusan = document.getElementById('filterJurusan').value.toLowerCase();
        const status = document.getElementById('filterStatus').value;
        
        let hasVisibleRows = false;
        
        allRows.forEach(row => {
            const rowKelas = row.children[3].textContent.toLowerCase();
            const rowJurusan = row.children[4].textContent.toLowerCase();
            const rowStatus = row.children[5].textContent.trim();
            
            const matchKelas = !kelas || rowKelas.includes(kelas);
            const matchJurusan = !jurusan || rowJurusan.includes(jurusan);
            const matchStatus = !status || rowStatus === status;
            
            if (matchKelas && matchJurusan && matchStatus) {
                row.style.display = '';
                hasVisibleRows = true;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide no data message
        let noDataMessage = document.querySelector('.no-data-message');
        if (!hasVisibleRows) {
            if (!noDataMessage) {
                noDataMessage = document.createElement('div');
                noDataMessage.className = 'alert alert-info no-data-message';
                noDataMessage.textContent = 'Tidak ada data yang sesuai dengan filter';
                tableBody.parentElement.insertAdjacentElement('afterend', noDataMessage);
            }
        } else if (noDataMessage) {
            noDataMessage.remove();
        }
    });
    
    // Reset Filter function
    document.getElementById('resetFilter').addEventListener('click', function() {
        filterForm.reset();
        allRows.forEach(row => row.style.display = '');
        const noDataMessage = document.querySelector('.no-data-message');
        if (noDataMessage) noDataMessage.remove();
    });
});
</script>
<script>
// Update the checkResetTime function
function checkResetTime() {
    const currentTime = new Date();
    const hours = currentTime.getHours();
    const minutes = currentTime.getMinutes();
    
    // Check if time is 17:00 or later
    if (hours >= 17) {
        fetch('check_reset.php')
            .then(response => response.json())
            .then(data => {
                if (data.should_notify) {
                    Swal.fire({
                        title: 'Perhatian!',
                        text: data.message,
                        icon: data.status === 'success' ? 'success' : 'error',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        if (data.status === 'success') {
                            location.reload();
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error checking reset time:', error);
            });
    }
}

// Check every minute
setInterval(checkResetTime, 60000);

// Initial check on page load
document.addEventListener('DOMContentLoaded', checkResetTime);
</script>
<script>
// Add this to your existing script section
function downloadExcel() {
    window.location.href = 'download_excel.php';
}
</script>
<script>
// Add this after your existing scripts
function updateRealTimeClock() {
    const clockElement = document.createElement('div');
    clockElement.className = 'real-time-clock';
    document.querySelector('.header-controls').prepend(clockElement);

    function updateClock() {
        const now = new Date();
        const time = now.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
        clockElement.innerHTML = `
            <i class="bi bi-clock"></i>
            <span>${time} WIB</span>
        `;
    }

    updateClock();
    setInterval(updateClock, 1000);
}

document.addEventListener('DOMContentLoaded', updateRealTimeClock);
</script>
</body>
</html>
