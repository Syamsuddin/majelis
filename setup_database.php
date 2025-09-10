<?php
// =================================================================
// Setup Database eMajelis dengan Sistem User
// =================================================================

// Include configuration
require_once 'config.php';

$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Buat database jika belum ada
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $db_name";
mysqli_query($koneksi, $sql_create_db);

// Pilih database
mysqli_select_db($koneksi, DB_NAME);

// Tabel users untuk sistem login
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    user_level ENUM('admin', 'operator', 'jemaah') NOT NULL,
    id_jemaah INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_jemaah) REFERENCES jemaah(id) ON DELETE SET NULL
)";

// Tabel jemaah (jika belum ada)
$sql_jemaah = "CREATE TABLE IF NOT EXISTS jemaah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_registrasi VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
    bin_binti VARCHAR(100),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Tabel kehadiran (jika belum ada)
$sql_kehadiran = "CREATE TABLE IF NOT EXISTS kehadiran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_jemaah INT NOT NULL,
    tanggal_hadir DATE NOT NULL,
    waktu_hadir TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_jemaah) REFERENCES jemaah(id) ON DELETE CASCADE
)";

// Tabel sumbangan (jika belum ada)
$sql_sumbangan = "CREATE TABLE IF NOT EXISTS sumbangan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kehadiran INT NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    tanggal_sumbangan DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kehadiran) REFERENCES kehadiran(id) ON DELETE CASCADE
)";

// Execute queries
$tables = [
    'jemaah' => $sql_jemaah,
    'users' => $sql_users,
    'kehadiran' => $sql_kehadiran,
    'sumbangan' => $sql_sumbangan
];

foreach ($tables as $table_name => $sql) {
    if (mysqli_query($koneksi, $sql)) {
        echo "Tabel $table_name berhasil dibuat atau sudah ada.<br>";
    } else {
        echo "Error membuat tabel $table_name: " . mysqli_error($koneksi) . "<br>";
    }
}

// Insert default admin user
$admin_username = 'admin';
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_name = 'Administrator Sistem';

$stmt_check_admin = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt_check_admin, "s", $admin_username);
mysqli_stmt_execute($stmt_check_admin);
$result = mysqli_stmt_get_result($stmt_check_admin);

if (mysqli_num_rows($result) == 0) {
    $stmt_insert_admin = mysqli_prepare($koneksi, "INSERT INTO users (username, password, nama_lengkap, user_level) VALUES (?, ?, ?, ?)");
    $user_level = 'admin';
    mysqli_stmt_bind_param($stmt_insert_admin, "ssss", $admin_username, $admin_password, $admin_name, $user_level);
    
    if (mysqli_stmt_execute($stmt_insert_admin)) {
        echo "User admin default berhasil dibuat.<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error membuat user admin: " . mysqli_error($koneksi) . "<br>";
    }
    mysqli_stmt_close($stmt_insert_admin);
} else {
    echo "User admin sudah ada.<br>";
}
mysqli_stmt_close($stmt_check_admin);

// Insert sample operator user
$operator_username = 'operator';
$operator_password = password_hash('operator123', PASSWORD_DEFAULT);
$operator_name = 'Operator Sistem';

$stmt_check_operator = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt_check_operator, "s", $operator_username);
mysqli_stmt_execute($stmt_check_operator);
$result = mysqli_stmt_get_result($stmt_check_operator);

if (mysqli_num_rows($result) == 0) {
    $stmt_insert_operator = mysqli_prepare($koneksi, "INSERT INTO users (username, password, nama_lengkap, user_level) VALUES (?, ?, ?, ?)");
    $operator_level = 'operator';
    mysqli_stmt_bind_param($stmt_insert_operator, "ssss", $operator_username, $operator_password, $operator_name, $operator_level);
    
    if (mysqli_stmt_execute($stmt_insert_operator)) {
        echo "User operator default berhasil dibuat.<br>";
        echo "Username: operator<br>";
        echo "Password: operator123<br>";
    } else {
        echo "Error membuat user operator: " . mysqli_error($koneksi) . "<br>";
    }
    mysqli_stmt_close($stmt_insert_operator);
} else {
    echo "User operator sudah ada.<br>";
}
mysqli_stmt_close($stmt_check_operator);

echo "<br><strong>Setup database selesai!</strong><br>";
echo "<a href='index.php'>Kembali ke Aplikasi</a>";

mysqli_close($koneksi);
?>