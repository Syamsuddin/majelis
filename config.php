<?php
// =================================================================
// Database Configuration - eMajelis
// =================================================================

// Session security configuration (hanya diatur jika session belum aktif)
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
    ini_set('session.cookie_samesite', 'Strict');
    
    // Set session timeout (30 minutes)
    ini_set('session.gc_maxlifetime', 1800);
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'emajelis');

// Create database connection
function createDatabaseConnection() {
    $koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$koneksi) {
        die("Koneksi ke database gagal: " . mysqli_connect_error());
    }
    
    return $koneksi;
}
?>