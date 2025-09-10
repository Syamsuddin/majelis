<?php
// =================================================================
// eMajelis - index.php (File Utama dengan Authentication)
// =================================================================
session_start();

// Include configuration
require_once 'config.php';

// Create database connection
$koneksi = createDatabaseConnection();

// Include auth functions
include 'auth_functions.php';

// Cek apakah user sudah login
requireLogin();

// Proses logout
if (isset($_GET['logout'])) {
    logoutUser();
}

// --- Fungsi Bantuan ---
function buatNomorRegistrasi($koneksi) {
    $query = "SELECT id FROM jemaah ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    $last_id = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $last_id = $row['id'];
    }
    $next_id = $last_id + 1;
    return 'MT-ALBA-' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
}

// --- Logika Pemrosesan Form ---

// 1. Proses Tambah Jemaah (Admin & Operator)
if (isset($_POST['tambah_jemaah'])) {
    requirePermission(['admin', 'operator']);
    
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $bin_binti = $_POST['bin_binti'];
    $alamat = $_POST['alamat'];
    $nomor_registrasi = buatNomorRegistrasi($koneksi);

    $stmt = mysqli_prepare($koneksi, "INSERT INTO jemaah (nomor_registrasi, nama, jenis_kelamin, bin_binti, alamat) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssss", $nomor_registrasi, $nama, $jenis_kelamin, $bin_binti, $alamat);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'Jemaah baru berhasil ditambahkan.'];
    } else {
        $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal menambahkan jemaah: ' . mysqli_error($koneksi)];
    }
    mysqli_stmt_close($stmt);
    header("Location: index.php?halaman=jemaah");
    exit();
}

// 1.1 Proses Edit Jemaah (Admin & Operator)
if (isset($_POST['edit_jemaah'])) {
    requirePermission(['admin', 'operator']);
    
    $id = (int)$_POST['id_jemaah'];
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $bin_binti = $_POST['bin_binti'];
    $alamat = $_POST['alamat'];

    $stmt = mysqli_prepare($koneksi, "UPDATE jemaah SET nama=?, jenis_kelamin=?, bin_binti=?, alamat=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssi", $nama, $jenis_kelamin, $bin_binti, $alamat, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'Data jemaah berhasil diperbarui.'];
    } else {
        $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal memperbarui data jemaah: ' . mysqli_error($koneksi)];
    }
    mysqli_stmt_close($stmt);
    header("Location: index.php?halaman=jemaah");
    exit();
}

// 1.2 Proses Hapus Jemaah (Admin & Operator)
if (isset($_POST['hapus_jemaah'])) {
    requirePermission(['admin', 'operator']);
    
    $id = (int)$_POST['id_jemaah'];
    
    // Cek apakah jemaah memiliki data kehadiran
    $stmt_check = mysqli_prepare($koneksi, "SELECT COUNT(*) as total FROM kehadiran WHERE id_jemaah = ?");
    mysqli_stmt_bind_param($stmt_check, "i", $id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $total_kehadiran = mysqli_fetch_assoc($result_check)['total'];
    mysqli_stmt_close($stmt_check);
    
    if ($total_kehadiran > 0) {
        $_SESSION['notifikasi'] = ['jenis' => 'warning', 'pesan' => 'Tidak dapat menghapus jemaah karena memiliki riwayat kehadiran. Hapus riwayat kehadiran terlebih dahulu.'];
    } else {
        // Hapus jemaah (akan otomatis mengupdate users yang terkait karena foreign key)
        $stmt_delete = mysqli_prepare($koneksi, "DELETE FROM jemaah WHERE id = ?");
        mysqli_stmt_bind_param($stmt_delete, "i", $id);
        if (mysqli_stmt_execute($stmt_delete)) {
            $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'Data jemaah berhasil dihapus.'];
        } else {
            $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal menghapus data jemaah: ' . mysqli_error($koneksi)];
        }
        mysqli_stmt_close($stmt_delete);
    }
    header("Location: index.php?halaman=jemaah");
    exit();
}

// 2. Proses Absensi via Scan Barcode (Admin & Operator)
if (isset($_POST['scan_barcode'])) {
    requirePermission(['admin', 'operator']);
    
    $nomor_registrasi = $_POST['nomor_registrasi'];
    // Ambil nilai sumbangan dari form, pastikan tipenya integer
    $nominal_sumbangan = isset($_POST['jumlah_sumbangan']) ? (int)$_POST['jumlah_sumbangan'] : 1000;

    if (empty($nomor_registrasi)) {
        $_SESSION['notifikasi_absensi'] = ['jenis' => 'warning', 'pesan' => 'Kolom Nomor Registrasi tidak boleh kosong.'];
        header("Location: index.php?halaman=absensi");
        exit();
    }

    $tanggal_sekarang = date('Y-m-d');
    $waktu_sekarang = date('H:i:s');
    
    $stmt_jemaah = mysqli_prepare($koneksi, "SELECT id, nama FROM jemaah WHERE nomor_registrasi = ?");
    mysqli_stmt_bind_param($stmt_jemaah, "s", $nomor_registrasi);
    mysqli_stmt_execute($stmt_jemaah);
    $result_jemaah = mysqli_stmt_get_result($stmt_jemaah);

    if (mysqli_num_rows($result_jemaah) > 0) {
        $jemaah = mysqli_fetch_assoc($result_jemaah);
        $id_jemaah = $jemaah['id'];
        $nama_jemaah = $jemaah['nama'];

        $stmt_cek = mysqli_prepare($koneksi, "SELECT id FROM kehadiran WHERE id_jemaah = ? AND tanggal_hadir = ?");
        mysqli_stmt_bind_param($stmt_cek, "is", $id_jemaah, $tanggal_sekarang);
        mysqli_stmt_execute($stmt_cek);
        $result_cek = mysqli_stmt_get_result($stmt_cek);

        if (mysqli_num_rows($result_cek) == 0) {
            mysqli_begin_transaction($koneksi);
            try {
                $stmt_hadir = mysqli_prepare($koneksi, "INSERT INTO kehadiran (id_jemaah, tanggal_hadir, waktu_hadir) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt_hadir, "iss", $id_jemaah, $tanggal_sekarang, $waktu_sekarang);
                mysqli_stmt_execute($stmt_hadir);
                $id_kehadiran = mysqli_insert_id($koneksi);
                mysqli_stmt_close($stmt_hadir);

                // Gunakan variabel $nominal_sumbangan yang didapat dari form
                $stmt_sumbangan = mysqli_prepare($koneksi, "INSERT INTO sumbangan (id_kehadiran, jumlah, tanggal_sumbangan) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt_sumbangan, "ids", $id_kehadiran, $nominal_sumbangan, $tanggal_sekarang);
                mysqli_stmt_execute($stmt_sumbangan);
                mysqli_stmt_close($stmt_sumbangan);

                mysqli_commit($koneksi);
                $_SESSION['notifikasi_absensi'] = ['jenis' => 'success', 'pesan' => "Kehadiran <strong>{$nama_jemaah}</strong> dengan sumbangan Rp ".number_format($nominal_sumbangan)." berhasil dicatat."];
            } catch (mysqli_sql_exception $exception) {
                mysqli_rollback($koneksi);
                $_SESSION['notifikasi_absensi'] = ['jenis' => 'danger', 'pesan' => 'Terjadi kesalahan saat mencatat data.'];
            }
        } else {
            $_SESSION['notifikasi_absensi'] = ['jenis' => 'warning', 'pesan' => "<strong>{$nama_jemaah}</strong> sudah tercatat hadir hari ini."];
        }
        mysqli_stmt_close($stmt_cek);
    } else {
        $_SESSION['notifikasi_absensi'] = ['jenis' => 'danger', 'pesan' => 'Nomor Registrasi tidak ditemukan.'];
    }
    mysqli_stmt_close($stmt_jemaah);
    header("Location: index.php?halaman=absensi");
    exit();
}

// 3. Proses Tambah Kehadiran Manual (Admin)
if (isset($_POST['tambah_kehadiran_manual'])) {
    requirePermission(['admin']);
    
    $nomor_registrasi = $_POST['nomor_registrasi'];
    $tanggal_hadir = $_POST['tanggal_hadir'];
    $waktu_hadir = $_POST['waktu_hadir'];
    $nominal_sumbangan = isset($_POST['jumlah_sumbangan']) ? (int)$_POST['jumlah_sumbangan'] : 0;

    if (empty($nomor_registrasi)) {
        $_SESSION['notifikasi'] = ['jenis' => 'warning', 'pesan' => 'Nomor Registrasi tidak boleh kosong.'];
        header("Location: index.php?halaman=kehadiran");
        exit();
    }
    
    // Cari jemaah berdasarkan nomor registrasi
    $stmt_jemaah = mysqli_prepare($koneksi, "SELECT id, nama FROM jemaah WHERE nomor_registrasi = ?");
    mysqli_stmt_bind_param($stmt_jemaah, "s", $nomor_registrasi);
    mysqli_stmt_execute($stmt_jemaah);
    $result_jemaah = mysqli_stmt_get_result($stmt_jemaah);

    if (mysqli_num_rows($result_jemaah) > 0) {
        $jemaah = mysqli_fetch_assoc($result_jemaah);
        $id_jemaah = $jemaah['id'];
        $nama_jemaah = $jemaah['nama'];

        // Cek apakah sudah ada kehadiran di tanggal yang sama
        $stmt_cek = mysqli_prepare($koneksi, "SELECT id FROM kehadiran WHERE id_jemaah = ? AND tanggal_hadir = ?");
        mysqli_stmt_bind_param($stmt_cek, "is", $id_jemaah, $tanggal_hadir);
        mysqli_stmt_execute($stmt_cek);
        $result_cek = mysqli_stmt_get_result($stmt_cek);

        if (mysqli_num_rows($result_cek) == 0) {
            mysqli_begin_transaction($koneksi);
            try {
                // Insert kehadiran
                $stmt_hadir = mysqli_prepare($koneksi, "INSERT INTO kehadiran (id_jemaah, tanggal_hadir, waktu_hadir) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt_hadir, "iss", $id_jemaah, $tanggal_hadir, $waktu_hadir);
                mysqli_stmt_execute($stmt_hadir);
                $id_kehadiran = mysqli_insert_id($koneksi);
                mysqli_stmt_close($stmt_hadir);

                // Insert sumbangan jika ada
                if ($nominal_sumbangan > 0) {
                    $stmt_sumbangan = mysqli_prepare($koneksi, "INSERT INTO sumbangan (id_kehadiran, jumlah, tanggal_sumbangan) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($stmt_sumbangan, "ids", $id_kehadiran, $nominal_sumbangan, $tanggal_hadir);
                    mysqli_stmt_execute($stmt_sumbangan);
                    mysqli_stmt_close($stmt_sumbangan);
                }

                mysqli_commit($koneksi);
                $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => "Kehadiran {$nama_jemaah} berhasil dicatat."];
            } catch (mysqli_sql_exception $exception) {
                mysqli_rollback($koneksi);
                $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Terjadi kesalahan saat mencatat data.'];
            }
        } else {
            $_SESSION['notifikasi'] = ['jenis' => 'warning', 'pesan' => "{$nama_jemaah} sudah tercatat hadir pada tanggal tersebut."];
        }
        mysqli_stmt_close($stmt_cek);
    } else {
        $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Nomor Registrasi tidak ditemukan.'];
    }
    mysqli_stmt_close($stmt_jemaah);
    header("Location: index.php?halaman=kehadiran");
    exit();
}

// 4. Proses Edit Kehadiran (Admin)
if (isset($_POST['edit_kehadiran'])) {
    requirePermission(['admin']);
    
    $id_kehadiran = (int)$_POST['id_kehadiran'];
    $id_jemaah = (int)$_POST['id_jemaah'];
    $tanggal_hadir = mysqli_real_escape_string($koneksi, $_POST['tanggal_hadir']);
    $waktu_hadir = mysqli_real_escape_string($koneksi, $_POST['waktu_hadir']);
    $nominal_sumbangan = isset($_POST['jumlah_sumbangan']) ? (int)$_POST['jumlah_sumbangan'] : 0;

    // Cek apakah kehadiran sudah ada untuk jemaah lain di tanggal yang sama
    $stmt_cek = mysqli_prepare($koneksi, "SELECT id FROM kehadiran WHERE id_jemaah = ? AND tanggal_hadir = ? AND id != ?");
    mysqli_stmt_bind_param($stmt_cek, "isi", $id_jemaah, $tanggal_hadir, $id_kehadiran);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($result_cek) == 0) {
        mysqli_begin_transaction($koneksi);
        try {
            // Update kehadiran
            $stmt_update = mysqli_prepare($koneksi, "UPDATE kehadiran SET id_jemaah=?, tanggal_hadir=?, waktu_hadir=? WHERE id=?");
            mysqli_stmt_bind_param($stmt_update, "issi", $id_jemaah, $tanggal_hadir, $waktu_hadir, $id_kehadiran);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);

            // Update atau insert sumbangan
            $stmt_cek_sumbangan = mysqli_prepare($koneksi, "SELECT id FROM sumbangan WHERE id_kehadiran = ?");
            mysqli_stmt_bind_param($stmt_cek_sumbangan, "i", $id_kehadiran);
            mysqli_stmt_execute($stmt_cek_sumbangan);
            $result_sumbangan = mysqli_stmt_get_result($stmt_cek_sumbangan);
            
            if (mysqli_num_rows($result_sumbangan) > 0) {
                // Update existing sumbangan
                if ($nominal_sumbangan > 0) {
                    $stmt_update_sumbangan = mysqli_prepare($koneksi, "UPDATE sumbangan SET jumlah=?, tanggal_sumbangan=? WHERE id_kehadiran=?");
                    mysqli_stmt_bind_param($stmt_update_sumbangan, "dsi", $nominal_sumbangan, $tanggal_hadir, $id_kehadiran);
                    mysqli_stmt_execute($stmt_update_sumbangan);
                    mysqli_stmt_close($stmt_update_sumbangan);
                } else {
                    // Delete sumbangan if amount is 0
                    $stmt_delete_sumbangan = mysqli_prepare($koneksi, "DELETE FROM sumbangan WHERE id_kehadiran=?");
                    mysqli_stmt_bind_param($stmt_delete_sumbangan, "i", $id_kehadiran);
                    mysqli_stmt_execute($stmt_delete_sumbangan);
                    mysqli_stmt_close($stmt_delete_sumbangan);
                }
            } else {
                // Insert new sumbangan if amount > 0
                if ($nominal_sumbangan > 0) {
                    $stmt_insert_sumbangan = mysqli_prepare($koneksi, "INSERT INTO sumbangan (id_kehadiran, jumlah, tanggal_sumbangan) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($stmt_insert_sumbangan, "ids", $id_kehadiran, $nominal_sumbangan, $tanggal_hadir);
                    mysqli_stmt_execute($stmt_insert_sumbangan);
                    mysqli_stmt_close($stmt_insert_sumbangan);
                }
            }
            mysqli_stmt_close($stmt_cek_sumbangan);

            mysqli_commit($koneksi);
            $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'Data kehadiran berhasil diperbarui.'];
        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($koneksi);
            $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal memperbarui data kehadiran: ' . mysqli_error($koneksi)];
        }
        mysqli_stmt_close($stmt_cek);
    } else {
        $_SESSION['notifikasi'] = ['jenis' => 'warning', 'pesan' => 'Jemaah sudah memiliki kehadiran di tanggal tersebut.'];
        mysqli_stmt_close($stmt_cek);
    }
    header("Location: index.php?halaman=kehadiran");
    exit();
}

// 5. Proses Hapus Kehadiran (Admin)
if (isset($_POST['hapus_kehadiran'])) {
    requirePermission(['admin']);
    
    $id_kehadiran = (int)$_POST['id_kehadiran'];
    
    mysqli_begin_transaction($koneksi);
    try {
        // Hapus sumbangan terkait terlebih dahulu
        $stmt_hapus_sumbangan = mysqli_prepare($koneksi, "DELETE FROM sumbangan WHERE id_kehadiran = ?");
        mysqli_stmt_bind_param($stmt_hapus_sumbangan, "i", $id_kehadiran);
        mysqli_stmt_execute($stmt_hapus_sumbangan);
        mysqli_stmt_close($stmt_hapus_sumbangan);
        
        // Hapus kehadiran
        $stmt_hapus_kehadiran = mysqli_prepare($koneksi, "DELETE FROM kehadiran WHERE id = ?");
        mysqli_stmt_bind_param($stmt_hapus_kehadiran, "i", $id_kehadiran);
        mysqli_stmt_execute($stmt_hapus_kehadiran);
        mysqli_stmt_close($stmt_hapus_kehadiran);
        
        mysqli_commit($koneksi);
        $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'Data kehadiran berhasil dihapus.'];
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($koneksi);
        $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal menghapus data kehadiran: ' . mysqli_error($koneksi)];
    }
    header("Location: index.php?halaman=kehadiran");
    exit();
}

// 6. Proses Tambah Sumbangan (Admin)
if (isset($_POST['tambah_sumbangan'])) {
    requirePermission(['admin']);
    
    $id_kehadiran = (int)$_POST['id_kehadiran'];
    $tanggal_sumbangan = mysqli_real_escape_string($koneksi, $_POST['tanggal_sumbangan']);
    $jumlah_sumbangan = (int)$_POST['jumlah_sumbangan'];

    if (empty($id_kehadiran) || empty($tanggal_sumbangan) || $jumlah_sumbangan <= 0) {
        $_SESSION['notifikasi'] = ['jenis' => 'warning', 'pesan' => 'Semua field harus diisi dengan benar.'];
        header("Location: index.php?halaman=sumbangan");
        exit();
    }
    
    // Cek apakah kehadiran ada
    $stmt_kehadiran = mysqli_prepare($koneksi, "SELECT id FROM kehadiran WHERE id = ?");
    mysqli_stmt_bind_param($stmt_kehadiran, "i", $id_kehadiran);
    mysqli_stmt_execute($stmt_kehadiran);
    $result_kehadiran = mysqli_stmt_get_result($stmt_kehadiran);

    if (mysqli_num_rows($result_kehadiran) > 0) {
        // Cek apakah sudah ada sumbangan untuk kehadiran ini
        $stmt_cek = mysqli_prepare($koneksi, "SELECT id FROM sumbangan WHERE id_kehadiran = ?");
        mysqli_stmt_bind_param($stmt_cek, "i", $id_kehadiran);
        mysqli_stmt_execute($stmt_cek);
        $result_cek = mysqli_stmt_get_result($stmt_cek);
        
        if (mysqli_num_rows($result_cek) == 0) {
            $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO sumbangan (id_kehadiran, jumlah, tanggal_sumbangan) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt_insert, "ids", $id_kehadiran, $jumlah_sumbangan, $tanggal_sumbangan);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'Data sumbangan berhasil ditambahkan.'];
            } else {
                $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal menambahkan data sumbangan: ' . mysqli_error($koneksi)];
            }
            mysqli_stmt_close($stmt_insert);
        } else {
            $_SESSION['notifikasi'] = ['jenis' => 'warning', 'pesan' => 'Kehadiran ini sudah memiliki data sumbangan.'];
        }
        mysqli_stmt_close($stmt_cek);
    } else {
        $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Data kehadiran tidak ditemukan.'];
    }
    mysqli_stmt_close($stmt_kehadiran);
    header("Location: index.php?halaman=sumbangan");
    exit();
}

// 7. Proses Edit Sumbangan (Admin)
if (isset($_POST['edit_sumbangan'])) {
    requirePermission(['admin']);
    
    $id_sumbangan = (int)$_POST['id_sumbangan'];
    $id_kehadiran = (int)$_POST['id_kehadiran'];
    $tanggal_sumbangan = mysqli_real_escape_string($koneksi, $_POST['tanggal_sumbangan']);
    $jumlah_sumbangan = (int)$_POST['jumlah_sumbangan'];

    if (empty($id_sumbangan) || empty($id_kehadiran) || empty($tanggal_sumbangan) || $jumlah_sumbangan <= 0) {
        $_SESSION['notifikasi'] = ['jenis' => 'warning', 'pesan' => 'Semua field harus diisi dengan benar.'];
        header("Location: index.php?halaman=sumbangan");
        exit();
    }
    
    // Cek apakah kehadiran ada
    $query_kehadiran = "SELECT id FROM kehadiran WHERE id = '$id_kehadiran'";
    $result_kehadiran = mysqli_query($koneksi, $query_kehadiran);

    if (mysqli_num_rows($result_kehadiran) > 0) {
        // Cek apakah ada sumbangan lain untuk kehadiran yang sama (kecuali yang sedang diedit)
        $query_cek = "SELECT id FROM sumbangan WHERE id_kehadiran = '$id_kehadiran' AND id != '$id_sumbangan'";
        $result_cek = mysqli_query($koneksi, $query_cek);
        
        if (mysqli_num_rows($result_cek) == 0) {
            $query_update = "UPDATE sumbangan SET id_kehadiran='$id_kehadiran', jumlah='$jumlah_sumbangan', tanggal_sumbangan='$tanggal_sumbangan' WHERE id='$id_sumbangan'";
            
            if (mysqli_query($koneksi, $query_update)) {
                $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'Data sumbangan berhasil diperbarui.'];
            } else {
                $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal memperbarui data sumbangan: ' . mysqli_error($koneksi)];
            }
        } else {
            $_SESSION['notifikasi'] = ['jenis' => 'warning', 'pesan' => 'Kehadiran tersebut sudah memiliki data sumbangan lain.'];
        }
    } else {
        $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Data kehadiran tidak ditemukan.'];
    }
    header("Location: index.php?halaman=sumbangan");
    exit();
}

// 8. Proses Hapus Sumbangan (Admin)
if (isset($_POST['hapus_sumbangan'])) {
    requirePermission(['admin']);
    
    $id_sumbangan = (int)$_POST['id_sumbangan'];
    
    $stmt_hapus = mysqli_prepare($koneksi, "DELETE FROM sumbangan WHERE id = ?");
    mysqli_stmt_bind_param($stmt_hapus, "i", $id_sumbangan);
    
    if (mysqli_stmt_execute($stmt_hapus)) {
        $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'Data sumbangan berhasil dihapus.'];
    } else {
        $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal menghapus data sumbangan: ' . mysqli_error($koneksi)];
    }
    mysqli_stmt_close($stmt_hapus);
    header("Location: index.php?halaman=sumbangan");
    exit();
}

// 9. Proses Hapus User (Admin)
if (isset($_POST['hapus_user'])) {
    requirePermission(['admin']);
    
    $user_id = (int)$_POST['user_id'];
    
    // Cek apakah user yang akan dihapus adalah admin terakhir
    $check_admin = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE user_level = 'admin' AND status = 'active' AND id != $user_id");
    $remaining_admin = mysqli_fetch_assoc($check_admin)['total'];
    
    // Cek apakah user yang akan dihapus adalah user yang sedang login
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['notifikasi'] = ['jenis' => 'warning', 'pesan' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.'];
    } elseif ($remaining_admin == 0) {
        // Cek jika user yang akan dihapus adalah admin
        $check_user_level = mysqli_query($koneksi, "SELECT user_level FROM users WHERE id = $user_id");
        $user_level_row = mysqli_fetch_assoc($check_user_level);
        
        if ($user_level_row && $user_level_row['user_level'] == 'admin') {
            $_SESSION['notifikasi'] = ['jenis' => 'warning', 'pesan' => 'Tidak dapat menghapus admin terakhir. Sistem harus memiliki minimal satu admin aktif.'];
        } else {
            // Hapus user (non-admin)
            $query = "DELETE FROM users WHERE id = $user_id";
            if (mysqli_query($koneksi, $query)) {
                $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'User berhasil dihapus (termasuk riwayat terkait jika ada).'];
            } else {
                $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal menghapus user: ' . mysqli_error($koneksi)];
            }
        }
    } else {
        // Hapus user (masih ada admin lain)
        $query = "DELETE FROM users WHERE id = $user_id";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'User berhasil dihapus (termasuk riwayat terkait jika ada).'];
        } else {
            $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal menghapus user: ' . mysqli_error($koneksi)];
        }
    }
    
    header("Location: index.php?halaman=users");
    exit();
}

// 10. Proses tambah user (Admin)
if (isset($_POST['tambah_user'])) {
    requirePermission(['admin']);
    
    $data = [
        'username' => $_POST['username'],
        'password' => $_POST['password'],
        'nama_lengkap' => $_POST['nama_lengkap'],
        'user_level' => $_POST['user_level'],
        'id_jemaah' => $_POST['id_jemaah'] ?? null
    ];
    
    $result = createUser($koneksi, $data);
    
    if ($result['success']) {
        $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => $result['message']];
    } else {
        $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => $result['message']];
    }
    
    header("Location: index.php?halaman=users");
    exit();
}

// 11. Proses update status user (Admin)
if (isset($_POST['update_status'])) {
    requirePermission(['admin']);
    
    $user_id = (int)$_POST['user_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE users SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "si", $status, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'Status user berhasil diperbarui'];
    } else {
        $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal memperbarui status user'];
    }
    
    header("Location: index.php?halaman=users");
    exit();
}

// 12. Proses edit user (Admin)
if (isset($_POST['edit_user'])) {
    requirePermission(['admin']);
    
    $user_id = (int)$_POST['user_id'];
    $username = $_POST['username'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $user_level = $_POST['user_level'];
    $id_jemaah = !empty($_POST['id_jemaah']) ? (int)$_POST['id_jemaah'] : null;
    
    // Cek apakah username sudah digunakan oleh user lain
    $check_query = "SELECT id FROM users WHERE username = ? AND id != ?";
    $stmt = mysqli_prepare($koneksi, $check_query);
    mysqli_stmt_bind_param($stmt, "si", $username, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Username sudah digunakan oleh user lain'];
    } else {
        // Update user data
        $update_query = "UPDATE users SET username = ?, nama_lengkap = ?, user_level = ?, id_jemaah = ? WHERE id = ?";
        $stmt = mysqli_prepare($koneksi, $update_query);
        mysqli_stmt_bind_param($stmt, "sssii", $username, $nama_lengkap, $user_level, $id_jemaah, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'Data user berhasil diperbarui'];
        } else {
            $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal memperbarui data user: ' . mysqli_error($koneksi)];
        }
    }
    
    header("Location: index.php?halaman=users");
    exit();
}

// 13. Proses reset password user (Admin)
if (isset($_POST['reset_password'])) {
    requirePermission(['admin']);
    
    $user_id = (int)$_POST['user_id'];
    $new_password = $_POST['new_password'];
    
    // Hash password baru
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $query = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notifikasi'] = ['jenis' => 'success', 'pesan' => 'Password user berhasil direset'];
    } else {
        $_SESSION['notifikasi'] = ['jenis' => 'danger', 'pesan' => 'Gagal mereset password user'];
    }
    
    header("Location: index.php?halaman=users");
    exit();
}

$halaman = isset($_GET['halaman']) ? $_GET['halaman'] : 'dashboard';

// Cek akses halaman
if (!canAccessPage($halaman, $_SESSION['user_level'])) {
    $_SESSION['notifikasi'] = ['jenis' => 'warning', 'pesan' => 'Anda tidak memiliki akses ke halaman tersebut.'];
    $halaman = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eMajelis - Sistem Informasi Majelis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f8f9fa; 
        }
        
        .sidebar { 
            width: 280px; 
            min-height: 100vh; 
            position: fixed;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 25%, #334155 50%, #475569 75%, #64748b 100%);
            background-size: 300% 300%;
            animation: navyGradient 8s ease infinite;
            box-shadow: 4px 0 15px rgba(15, 23, 42, 0.2);
        }
        
        @keyframes navyGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, 
                rgba(255,255,255,0.05) 0%, 
                transparent 30%, 
                rgba(255,255,255,0.08) 60%, 
                transparent 100%);
            pointer-events: none;
        }
        
        .sidebar .nav-link { 
            font-size: 1rem; 
            padding: 0.9rem 1.2rem; 
            border-radius: 12px; 
            transition: all 0.3s ease;
            margin: 0.2rem 0;
            position: relative;
            backdrop-filter: blur(10px);
        }
        
        .sidebar .nav-link:hover { 
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-link.active { 
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
            transform: translateX(8px);
        }
        
        .sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: -12px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: #3b82f6;
            border-radius: 2px;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }
        
        .sidebar .nav-link i { 
            margin-right: 12px; 
            font-size: 1.1rem;
        }
        
        .sidebar hr {
            border-color: rgba(255, 255, 255, 0.2);
            margin: 1.5rem 0;
        }
        
        .sidebar .bg-secondary {
            background: rgba(255, 255, 255, 0.12) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .content { 
            margin-left: 280px; 
            padding: 2rem; 
        }
        
        .card { 
            border: none; 
            border-radius: 0.75rem; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); 
        }
        
        .card-title { 
            font-weight: 600; 
        }
        
        .stat-icon { 
            font-size: 2.5rem; 
            opacity: 0.3; 
        }
        
        /* Islamic decorative elements */
        .sidebar .fs-4 {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            font-weight: 600;
        }
        
        .sidebar .bi-moon-stars-fill {
            color: #60a5fa;
            filter: drop-shadow(0 0 8px rgba(96, 165, 250, 0.4));
        }
        
        .sidebar .logout-btn {
            background: rgba(220, 38, 38, 0.15);
            border: 1px solid rgba(220, 38, 38, 0.3);
            color: #fca5a5;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .sidebar .logout-btn:hover {
            background: rgba(220, 38, 38, 0.25);
            color: #fecaca;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
            }
            .content {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- SIDEBAR NAVIGASI -->
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3 text-white">
        <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <i class="bi bi-moon-stars-fill me-2 fs-4"></i>
            <span class="fs-4">eMajelis</span>
        </a>
        <hr>
        
        <!-- Info User -->
        <div class="mb-3 p-2 bg-secondary rounded">
            <div class="d-flex align-items-center">
                <i class="bi bi-person-circle me-2" style="font-size: 1.5rem;"></i>
                <div>
                    <div class="fw-bold small"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></div>
                    <small class="text-light opacity-75"><?= ucfirst($_SESSION['user_level']) ?></small>
                </div>
            </div>
        </div>
        
        <!-- Menu Navigasi Dinamis -->
        <ul class="nav nav-pills flex-column mb-auto">
            <?php
            $user_menus = getMenuForUser($_SESSION['user_level']);
            foreach ($user_menus as $menu_key => $menu_data):
            ?>
            <li class="nav-item mb-2">
                <a href="index.php?halaman=<?= $menu_key ?>" 
                   class="nav-link text-white <?php echo ($halaman == $menu_key) ? 'active' : ''; ?>">
                    <i class="bi <?= $menu_data['icon'] ?>"></i> <?= $menu_data['label'] ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <hr>
        
        <!-- Logout Button -->
        <div class="mt-auto">
            <a href="index.php?logout=1" 
               class="d-flex align-items-center logout-btn" 
               onclick="return confirm('Yakin ingin logout?')">
                <i class="bi bi-box-arrow-right me-2"></i>
                <span>Logout</span>
            </a>
        </div>
        
        <div class="mt-2">
            <span class="text-light small opacity-75" style="text-shadow: 0 1px 2px rgba(0,0,0,0.5);">Versi 2.0</span>
        </div>
    </div>

    <!-- KONTEN UTAMA -->
    <main class="content flex-grow-1">
        <?php
        if (isset($_SESSION['notifikasi'])) {
            echo '<div class="alert alert-' . $_SESSION['notifikasi']['jenis'] . ' alert-dismissible fade show" role="alert">
                    ' . $_SESSION['notifikasi']['pesan'] . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['notifikasi']);
        }
        
        $file_halaman = "halaman/{$halaman}.php";
        if (file_exists($file_halaman)) {
            include $file_halaman;
        } else {
            echo "<div class='alert alert-danger'>Halaman tidak ditemukan.</div>";
        }
        ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    function tampilkanKartu(nomorRegistrasi, nama) {
        document.getElementById('nama-kartu').innerText = nama;
        document.getElementById('nomor-registrasi-kartu').innerText = nomorRegistrasi;
        JsBarcode("#barcode", nomorRegistrasi, { format: "CODE128", lineColor: "#000", width: 2, height: 80, displayValue: false });
        var myModal = new bootstrap.Modal(document.getElementById('kartuModal'));
        myModal.show();
    }

    function toggleDateInputs(periode) {
        if (periode === 'bulanan') {
            document.getElementById('input-harian-mingguan').classList.add('d-none');
            document.getElementById('input-bulanan').classList.remove('d-none');
        } else {
            document.getElementById('input-harian-mingguan').classList.remove('d-none');
            document.getElementById('input-bulanan').classList.add('d-none');
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const periodeSelect = document.getElementById('periode');
        if (periodeSelect) {
            toggleDateInputs(periodeSelect.value);
        }
    });
</script>

</body>
</html>