<?php
include 'config.php';

$backgrounds = [
    'assets/ISRAMIRAJ.jpg',
    'assets/LDKS.jpg',
    'assets/UPACARA.jpg',
    'assets/BPBD.jpg'
];

$audioFile = 'assets/bsorkestra.mp3';
$audioExists = file_exists($audioFile);

if (!$audioExists) {
    error_log("Background music file not found: $audioFile");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    switch($user_type) {
        case 'admin':
            // Admin login
            $query = $conn->prepare("SELECT * FROM admin WHERE username = ?");
            $query->bind_param("s", $username);
            $query->execute();
            $result = $query->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($password == $row['password']) {
                    $_SESSION['admin'] = $username;
                    header("Location: dashboard_admin.php");
                    exit();
                }
            }
            $error_message = "Username atau password admin salah!";
            break;

        case 'guru':
    // Guru login
    $query = $conn->prepare("SELECT * FROM guru WHERE nip = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($password == $row['password']) {
            // Set all necessary session variables
            $_SESSION['guru_id'] = $row['id'];
            $_SESSION['guru_nama'] = $row['nama'];
            $_SESSION['guru_nip'] = $row['nip'];
            $_SESSION['guru_login'] = true;
            $_SESSION['guru_mata_pelajaran'] = $row['mata_pelajaran'];
            
            // Redirect to dashboard_guru.php
            header("Location: dashboard_guru.php");
            exit();
        }
    }
    $error_message = "NIP atau password guru salah!";
    break;

        case 'siswa':
            try {
                // Siswa login
                $query = $conn->prepare("SELECT * FROM siswa WHERE nis = ?");
                $query->bind_param("s", $username);
                $query->execute();
                $result = $query->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    if ($password == $row['password']) {
                        $_SESSION['siswa_id'] = $row['id'];
                        $_SESSION['siswa_nama'] = $row['nama'];
                        header("Location: dashboard_siswa.php");
                        exit();
                    }
                }
                $error_message = "NIS atau password siswa salah!";
            } catch (mysqli_sql_exception $e) {
                $error_message = "Terjadi kesalahan pada sistem. Silakan coba lagi nanti.";
                error_log("Database Error: " . $e->getMessage());
            }
            break;

        default:
            $error_message = "Tipe pengguna tidak valid!";
            break;
    }
}

// ...rest of the existing HTML code...
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMK Negeri 40 Jakarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #283593;
            --accent-color: #3949ab;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .bg-slider {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0;
            transition: opacity 1s ease;
        }

        .bg-slider.loaded {
            opacity: 1;
        }

        .bg-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .bg-slide.active {
            opacity: 1;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .school-logo {
            width: 120px;
            height: auto;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .school-logo:hover {
            transform: scale(1.05);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .user-type-toggle {
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .btn-check + .btn {
            border: none;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-check:checked + .btn-outline-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .input-group {
            margin-bottom: 1.25rem;
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.9);
            border-right: none;
            color: var(--primary-color);
            cursor: pointer;
            transition: var(--transition);
        }

        #togglePassword:hover {
            color: var(--accent-color);
        }

        .input-group-text i {
            transition: var(--transition);
        }

        #togglePassword:active i {
            transform: scale(0.9);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border-left: none;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .alert {
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 2rem;
            }

            .school-logo {
                width: 100px;
            }
        }

        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .loading-screen.fade-out {
            opacity: 0;
            visibility: hidden;
            transform: scale(1.1);
        }

        .loading-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        .loading-logo {
            width: 120px;
            height: 120px;
            position: relative;
            animation: float 3s ease-in-out infinite;
        }

        .loading-logo::before {
            content: '';
            position: absolute;
            width: 140%;
            height: 140%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            left: -20%;
            top: -20%;
            animation: pulse 2s ease-in-out infinite;
        }

        .loading-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.3));
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-text {
            color: white;
            font-size: 1rem;
            font-weight: 500;
            letter-spacing: 2px;
            text-align: center;
            opacity: 0.9;
            animation: pulse-text 2s ease-in-out infinite;
        }

        .loading-progress {
            width: 200px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            margin-top: 1rem;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: white;
            border-radius: 4px;
            width: 0;
            transition: width 0.3s ease;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.2); opacity: 0.1; }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes pulse-text {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        .sound-control {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .btn-sound {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-sound:hover {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 0.3);
        }

        .btn-sound i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .btn-sound:active i {
            transform: scale(0.9);
        }

        .btn-sound.muted i {
            color: #dc3545;
        }

        @keyframes pulse-sound {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .btn-sound:not(.muted) i {
            animation: pulse-sound 2s infinite;
        }

        /* Update the audio element styling */
        #bgMusic {
            opacity: 0;
            visibility: hidden;
            position: fixed;
        }

        /* Add this to your existing styles section */
        .back-to-home {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
            color: white;
        }

        .btn-back i {
            transition: transform 0.3s ease;
        }

        .btn-back:hover i {
            transform: translateX(-3px);
        }
    </style>
</head>
<body>
    <!-- Remove duplicate elements and keep only one set -->
    <audio id="bgMusic" loop preload="metadata" autoplay muted>
        <source src="<?= $audioFile ?>" type="audio/mpeg">
    </audio>

    <div class="sound-control">
        <button id="soundToggle" class="btn-sound">
            <i class="bi bi-volume-up-fill"></i>
        </button>
    </div>
    <div class="loading-screen">
        <div class="loading-content">
            <div class="loading-logo">
                <img src="Logo 40.png" alt="Logo SMK 40">
            </div>
            <div class="loading-spinner"></div>
            <div class="loading-text">Memuat Sistem Absensi</div>
            <div class="loading-progress">
                <div class="progress-bar"></div>
            </div>
        </div>
    </div>
    <div class="bg-slider">
        <?php foreach ($backgrounds as $index => $bg): ?>
        <div class="bg-slide <?= $index === 0 ? 'active' : '' ?>" 
             style="background-image: url('<?= $bg ?>');">
        </div>
        <?php endforeach; ?>
    </div>
    <div class="login-container">
        <div class="login-header">
            <img src="Logo 40.png" alt="SMK Negeri 40 Jakarta" class="school-logo">
            <h2>Selamat Datang</h2>
            <p>Silakan login untuk melanjutkan</p>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?= $error_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="user-type-toggle d-flex gap-2">
            <input type="radio" class="btn-check" name="user_type" id="admin" value="admin" checked>
            <label class="btn btn-outline-primary flex-grow-1" for="admin">
                <i class="bi bi-person-gear me-2"></i>Admin
            </label>
            
            <input type="radio" class="btn-check" name="user_type" id="guru" value="guru">
            <label class="btn btn-outline-primary flex-grow-1" for="guru">
                <i class="bi bi-person-workspace me-2"></i>Guru
            </label>
            
            <input type="radio" class="btn-check" name="user_type" id="siswa" value="siswa">
            <label class="btn btn-outline-primary flex-grow-1" for="siswa">
                <i class="bi bi-mortarboard me-2"></i>Siswa
            </label>
        </div>

        <form method="POST" class="login-form">
            <input type="hidden" name="user_type" id="user_type_input" value="admin">
            
            <div class="input-group">
                <span class="input-group-text border-end-0">
                    <i class="bi bi-person"></i>
                </span>
                <input type="text" name="username" class="form-control border-start-0" id="username_field" placeholder="Username" required>
            </div>
            
            <div class="input-group">
                <span class="input-group-text border-end-0">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password" name="password" class="form-control border-start-0 border-end-0" id="password" placeholder="Password" required>
                <span class="input-group-text border-start-0" style="cursor: pointer;" id="togglePassword">
                    <i class="bi bi-eye-slash" id="toggleIcon"></i>
                </span>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </button>
        </form>
    </div>

    <!-- Add this after the body tag -->
    <div class="back-to-home">
        <a href="index.php" class="btn-back">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[name="user_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('user_type_input').value = this.value;
                const placeholders = {
                    'admin': 'Username',
                    'guru': 'NIP',
                    'siswa': 'NIS'
                };
                document.getElementById('username_field').placeholder = placeholders[this.value];
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.bg-slide');
            let currentSlide = 0;
            
            function nextSlide() {
                slides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add('active');
            }
            
            // Change background every 5 seconds
            setInterval(nextSlide, 5000);
        });
    </script>
    <script>
        // Add this after your existing scripts
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            togglePassword.addEventListener('click', function () {
                // Toggle password visibility
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Toggle icon
                toggleIcon.classList.toggle('bi-eye');
                toggleIcon.classList.toggle('bi-eye-slash');
            });
        });
    </script>
    <script>
// Update the loading screen JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const loadingScreen = document.querySelector('.loading-screen');
    const bgSlider = document.querySelector('.bg-slider');
    const progressBar = document.querySelector('.progress-bar');
    const loadingText = document.querySelector('.loading-text');
    let imagesLoaded = 0;
    const totalImages = <?= count($backgrounds) ?>;
    const loadingTexts = [
        'Mempersiapkan Sistem...',
        'Memuat Data...',
        'Hampir Selesai...'
    ];
    let textIndex = 0;

    function updateLoadingText() {
        if (textIndex < loadingTexts.length) {
            loadingText.style.opacity = '0';
            setTimeout(() => {
                loadingText.textContent = loadingTexts[textIndex];
                loadingText.style.opacity = '1';
                textIndex++;
            }, 300);
        }
    }

    function preloadImages(urls) {
        urls.forEach(url => {
            const img = new Image();
            img.src = url;
            img.onload = () => {
                imagesLoaded++;
                const progress = (imagesLoaded / totalImages) * 100;
                progressBar.style.width = `${progress}%`;
                
                if (imagesLoaded === totalImages) {
                    // Complete loading sequence
                    setTimeout(() => {
                        progressBar.style.width = '100%';
                        updateLoadingText();
                        
                        setTimeout(() => {
                            loadingScreen.classList.add('fade-out');
                            bgSlider.classList.add('loaded');
                            
                            setTimeout(() => {
                                loadingScreen.style.display = 'none';
                            }, 500);
                        }, 500);
                    }, 1000);
                } else if (imagesLoaded === Math.floor(totalImages / 2)) {
                    updateLoadingText();
                }
            };
        });
    }

    // Start loading sequence
    updateLoadingText();
    setInterval(updateLoadingText, 2000);
    
    preloadImages([
        <?php foreach($backgrounds as $bg): ?>
            '<?= $bg ?>',
        <?php endforeach; ?>
    ]);
});
</script>
    <script>
// Replace the existing sound control script
document.addEventListener('DOMContentLoaded', function() {
    const bgMusic = document.getElementById('bgMusic');
    const soundToggle = document.getElementById('soundToggle');
    const soundIcon = soundToggle.querySelector('i');
    
    // Function to play audio
    async function playAudio() {
        try {
            bgMusic.muted = false;
            await bgMusic.play();
            soundToggle.classList.remove('muted');
            soundIcon.classList.remove('bi-volume-mute-fill');
            soundIcon.classList.add('bi-volume-up-fill');
        } catch (err) {
            console.log('Playback failed:', err);
        }
    }

    // Function to mute audio
    function muteAudio() {
        bgMusic.muted = true;
        soundToggle.classList.add('muted');
        soundIcon.classList.remove('bi-volume-up-fill');
        soundIcon.classList.add('bi-volume-mute-fill');
    }

    // Function to toggle sound
    function toggleSound() {
        if (bgMusic.muted) {
            playAudio();
        } else {
            muteAudio();
        }
    }

    // Add click event listener
    soundToggle.addEventListener('click', toggleSound);

    // Initial play attempt
    window.addEventListener('load', function() {
        setTimeout(() => {
            playAudio();
        }, 1000);
    });

    // Play after user interaction
    document.addEventListener('click', function initAudio() {
        playAudio();
        document.removeEventListener('click', initAudio);
    }, { once: true });

    // Handle visibility changes
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && !bgMusic.muted) {
            bgMusic.play();
        }
    });

    // Play after loading screen
    const loadingScreen = document.querySelector('.loading-screen');
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.target.style.display === 'none') {
                playAudio();
                observer.disconnect();
            }
        });
    });

    observer.observe(loadingScreen, {
        attributes: true,
        attributeFilter: ['style']
    });

    // Set initial volume
    bgMusic.volume = 0.7;
});
</script>
</body>
</html>