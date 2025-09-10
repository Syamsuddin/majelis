<?php
// Ambil data untuk statistik dashboard berdasarkan level user
$today = date('Y-m-d');
$stats = getDashboardStats($koneksi, $_SESSION['user_level'], $_SESSION['user_id']);
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Dashboard</h1>
        <div class="text-muted">
            <i class="bi bi-calendar3 me-2"></i><?= date('d F Y') ?>
        </div>
    </div>
    
    <!-- Selamat Datang -->
    <div class="alert alert-primary border-left-primary" role="alert">
        <h4 class="alert-heading">Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!</h4>
        <p class="mb-0">
            <?php
            switch($_SESSION['user_level']) {
                case 'admin':
                    echo 'Anda memiliki akses penuh ke semua fitur sistem eMajelis.';
                    break;
                case 'operator':
                    echo 'Anda dapat mengelola data jemaah dan melakukan input absensi.';
                    break;
                case 'jemaah':
                    echo 'Selamat datang di sistem eMajelis. Anda dapat melihat profil dan riwayat kehadiran Anda.';
                    break;
            }
            ?>
        </p>
    </div>

    <!-- Statistik berdasarkan level user -->
    <div class="row">
        <?php if ($_SESSION['user_level'] == 'admin'): ?>
            <!-- Admin - Statistik Lengkap -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start border-primary border-4 h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Jemaah</div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= $stats['total_jemaah'] ?> Orang</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people-fill stat-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start border-success border-4 h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Hadir Hari Ini</div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= $stats['total_kehadiran_hari_ini'] ?> Orang</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-check-circle-fill stat-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start border-info border-4 h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">Total Users</div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= $stats['total_users'] ?> Users</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-person-gear stat-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start border-warning border-4 h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Sumbangan Bulan Ini</div>
                                <div class="h6 mb-0 fw-bold text-gray-800">Rp <?= number_format($stats['total_sumbangan_bulan_ini'], 0, ',', '.') ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-wallet2 stat-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php elseif ($_SESSION['user_level'] == 'operator'): ?>
            <!-- Operator - Statistik Terbatas -->
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-start border-primary border-4 h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Jemaah</div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= $stats['total_jemaah'] ?> Orang</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people-fill stat-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-start border-success border-4 h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Hadir Hari Ini</div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= $stats['total_kehadiran_hari_ini'] ?> Orang</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-check-circle-fill stat-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php elseif ($_SESSION['user_level'] == 'jemaah'): ?>
            <!-- Jemaah - Statistik Pribadi -->
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-start border-primary border-4 h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Kehadiran Saya</div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= $stats['total_kehadiran'] ?> kali</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-check stat-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-start border-success border-4 h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Kehadiran Bulan Ini</div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= $stats['kehadiran_bulan_ini'] ?> kali</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-month stat-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-12 col-md-12 mb-4">
                <div class="card border-start border-info border-4 h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">Total Sumbangan Saya</div>
                                <div class="h5 mb-0 fw-bold text-gray-800">Rp <?= number_format($stats['total_sumbangan'], 0, ',', '.') ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-wallet2 stat-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Info Majelis -->
    <div class="card mt-4">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-calendar-event-fill me-2"></i>Jadwal Majelis Rutin
        </div>
        <div class="card-body">
            <h5 class="card-title">Majelis Al Baladul Amin</h5>
            <p class="card-text">Setiap hari <strong>Kamis Sore</strong>, ba'da Ashar.</p>
            <?php if ($_SESSION['user_level'] == 'admin' || $_SESSION['user_level'] == 'operator'): ?>
                <a href="index.php?halaman=absensi" class="btn btn-success">
                    <i class="bi bi-qr-code-scan me-2"></i>Mulai Absensi Sekarang
                </a>
            <?php endif; ?>
            <?php if ($_SESSION['user_level'] == 'jemaah'): ?>
                <a href="index.php?halaman=profile" class="btn btn-primary">
                    <i class="bi bi-person-circle me-2"></i>Lihat Profil Saya
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>