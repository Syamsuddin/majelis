<?php
// =================================================================
// Auth Functions - Fungsi Authentication dan Authorization
// =================================================================

// Fungsi untuk cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi untuk cek level user
function hasPermission($required_levels) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_string($required_levels)) {
        $required_levels = [$required_levels];
    }
    
    return in_array($_SESSION['user_level'], $required_levels);
}

// Fungsi untuk redirect jika tidak memiliki akses
function requirePermission($required_levels, $redirect_url = 'login.php') {
    if (!hasPermission($required_levels)) {
        header("Location: $redirect_url");
        exit();
    }
}

// Fungsi untuk redirect jika belum login
function requireLogin($redirect_url = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect_url");
        exit();
    }
}

// Fungsi untuk login user
function loginUser($koneksi, $username, $password) {
    $username = mysqli_real_escape_string($koneksi, $username);
    
    $query = "SELECT u.*, j.nama as nama_jemaah, j.nomor_registrasi 
              FROM users u 
              LEFT JOIN jemaah j ON u.id_jemaah = j.id 
              WHERE u.username = ? AND u.status = 'active'";
    
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['user_level'] = $user['user_level'];
            $_SESSION['id_jemaah'] = $user['id_jemaah'];
            $_SESSION['nama_jemaah'] = $user['nama_jemaah'];
            $_SESSION['nomor_registrasi'] = $user['nomor_registrasi'];
            $_SESSION['login_time'] = time();
            
            return ['success' => true, 'user' => $user];
        }
    }
    
    return ['success' => false, 'message' => 'Username atau password salah'];
}

// Fungsi untuk logout user
function logoutUser() {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Fungsi untuk mendapatkan menu berdasarkan level user
function getMenuForUser($user_level) {
    $menus = [
        'admin' => [
            'dashboard' => ['icon' => 'bi-grid-1x2-fill', 'label' => 'Dashboard'],
            'jemaah' => ['icon' => 'bi-people-fill', 'label' => 'Jemaah'],
            'absensi' => ['icon' => 'bi-qr-code-scan', 'label' => 'Absensi'],
            'kehadiran' => ['icon' => 'bi-calendar-check-fill', 'label' => 'Kelola Kehadiran'],
            'sumbangan' => ['icon' => 'bi-wallet2', 'label' => 'Kelola Sumbangan'],
            'rekap' => ['icon' => 'bi-clipboard-data-fill', 'label' => 'Rekap Data'],
            'users' => ['icon' => 'bi-person-gear', 'label' => 'Kelola User']
        ],
        'operator' => [
            'dashboard' => ['icon' => 'bi-grid-1x2-fill', 'label' => 'Dashboard'],
            'jemaah' => ['icon' => 'bi-people-fill', 'label' => 'Jemaah'],
            'absensi' => ['icon' => 'bi-qr-code-scan', 'label' => 'Absensi'],
            'rekap' => ['icon' => 'bi-clipboard-data-fill', 'label' => 'Rekap Data']
        ],
        'jemaah' => [
            'dashboard' => ['icon' => 'bi-grid-1x2-fill', 'label' => 'Dashboard'],
            'profile' => ['icon' => 'bi-person-circle', 'label' => 'Profil Saya']
        ]
    ];
    
    return $menus[$user_level] ?? [];
}

// Fungsi untuk cek akses halaman
function canAccessPage($page, $user_level) {
    $page_permissions = [
        'dashboard' => ['admin', 'operator', 'jemaah'],
        'jemaah' => ['admin', 'operator'],
        'absensi' => ['admin', 'operator'],
        'kehadiran' => ['admin'],
        'sumbangan' => ['admin'],
        'rekap' => ['admin', 'operator'],
        'users' => ['admin'],
        'profile' => ['jemaah']
    ];
    
    return isset($page_permissions[$page]) && in_array($user_level, $page_permissions[$page]);
}

// Fungsi untuk membuat user baru
function createUser($koneksi, $data) {
    $username = mysqli_real_escape_string($koneksi, $data['username']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $data['nama_lengkap']);
    $user_level = mysqli_real_escape_string($koneksi, $data['user_level']);
    $id_jemaah = !empty($data['id_jemaah']) ? (int)$data['id_jemaah'] : null;
    
    // Cek apakah username sudah ada
    $check_query = "SELECT id FROM users WHERE username = ?";
    $stmt = mysqli_prepare($koneksi, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        return ['success' => false, 'message' => 'Username sudah digunakan'];
    }
    
    // Insert user baru
    $insert_query = "INSERT INTO users (username, password, nama_lengkap, user_level, id_jemaah) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $insert_query);
    mysqli_stmt_bind_param($stmt, "ssssi", $username, $password, $nama_lengkap, $user_level, $id_jemaah);
    
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'message' => 'User berhasil dibuat'];
    } else {
        return ['success' => false, 'message' => 'Gagal membuat user: ' . mysqli_error($koneksi)];
    }
}

// Fungsi untuk mengambil data jemaah berdasarkan user ID
function getJemaahByUserId($koneksi, $user_id) {
    $query = "SELECT j.*, u.username 
              FROM jemaah j 
              INNER JOIN users u ON j.id = u.id_jemaah 
              WHERE u.id = ?";
    
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk mendapatkan statistik dashboard berdasarkan level user
function getDashboardStats($koneksi, $user_level, $user_id = null) {
    $stats = [];
    
    if ($user_level == 'admin') {
        // Admin bisa lihat semua statistik
        $stats['total_jemaah'] = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM jemaah"))['count'];
        $stats['total_kehadiran_hari_ini'] = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM kehadiran WHERE tanggal_hadir = CURDATE()"))['count'];
        $stats['total_users'] = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM users WHERE status = 'active'"))['count'];
        $stats['total_sumbangan_bulan_ini'] = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(jumlah), 0) as total FROM sumbangan WHERE MONTH(tanggal_sumbangan) = MONTH(CURDATE()) AND YEAR(tanggal_sumbangan) = YEAR(CURDATE())"))['total'];
    } elseif ($user_level == 'operator') {
        // Operator bisa lihat statistik terbatas
        $stats['total_jemaah'] = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM jemaah"))['count'];
        $stats['total_kehadiran_hari_ini'] = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM kehadiran WHERE tanggal_hadir = CURDATE()"))['count'];
    } elseif ($user_level == 'jemaah') {
        // Jemaah hanya bisa lihat statistik pribadi
        $user_jemaah = getJemaahByUserId($koneksi, $user_id);
        if ($user_jemaah) {
            $id_jemaah = $user_jemaah['id'];
            
            $stmt1 = mysqli_prepare($koneksi, "SELECT COUNT(*) as count FROM kehadiran WHERE id_jemaah = ?");
            mysqli_stmt_bind_param($stmt1, "i", $id_jemaah);
            mysqli_stmt_execute($stmt1);
            $stats['total_kehadiran'] = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt1))['count'];
            mysqli_stmt_close($stmt1);
            
            $stmt2 = mysqli_prepare($koneksi, "SELECT COUNT(*) as count FROM kehadiran WHERE id_jemaah = ? AND MONTH(tanggal_hadir) = MONTH(CURDATE()) AND YEAR(tanggal_hadir) = YEAR(CURDATE())");
            mysqli_stmt_bind_param($stmt2, "i", $id_jemaah);
            mysqli_stmt_execute($stmt2);
            $stats['kehadiran_bulan_ini'] = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2))['count'];
            mysqli_stmt_close($stmt2);
            
            $stmt3 = mysqli_prepare($koneksi, "SELECT COALESCE(SUM(s.jumlah), 0) as total FROM sumbangan s INNER JOIN kehadiran k ON s.id_kehadiran = k.id WHERE k.id_jemaah = ?");
            mysqli_stmt_bind_param($stmt3, "i", $id_jemaah);
            mysqli_stmt_execute($stmt3);
            $stats['total_sumbangan'] = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt3))['total'];
            mysqli_stmt_close($stmt3);
        }
    }
    
    return $stats;
}
?>