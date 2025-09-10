<?php
// ===================================================================
// HALAMAN PROFIL JEMAAH
// ===================================================================

// Cek akses jemaah
requirePermission(['jemaah']);

// Ambil data jemaah berdasarkan user yang login
$jemaah_data = getJemaahByUserId($koneksi, $_SESSION['user_id']);

if (!$jemaah_data) {
    echo '<div class="alert alert-danger">Data jemaah tidak ditemukan!</div>';
    return;
}

// Ambil statistik kehadiran
$id_jemaah = $jemaah_data['id'];

// Total kehadiran
$query_total_kehadiran = "SELECT COUNT(*) as total FROM kehadiran WHERE id_jemaah = $id_jemaah";
$total_kehadiran = mysqli_fetch_assoc(mysqli_query($koneksi, $query_total_kehadiran))['total'];

// Kehadiran bulan ini
$query_kehadiran_bulan = "SELECT COUNT(*) as total FROM kehadiran 
                          WHERE id_jemaah = $id_jemaah 
                          AND MONTH(tanggal_hadir) = MONTH(CURDATE()) 
                          AND YEAR(tanggal_hadir) = YEAR(CURDATE())";
$kehadiran_bulan_ini = mysqli_fetch_assoc(mysqli_query($koneksi, $query_kehadiran_bulan))['total'];

// Total sumbangan
$query_total_sumbangan = "SELECT COALESCE(SUM(s.jumlah), 0) as total 
                          FROM sumbangan s 
                          INNER JOIN kehadiran k ON s.id_kehadiran = k.id 
                          WHERE k.id_jemaah = $id_jemaah";
$total_sumbangan = mysqli_fetch_assoc(mysqli_query($koneksi, $query_total_sumbangan))['total'];

// Sumbangan bulan ini
$query_sumbangan_bulan = "SELECT COALESCE(SUM(s.jumlah), 0) as total 
                          FROM sumbangan s 
                          INNER JOIN kehadiran k ON s.id_kehadiran = k.id 
                          WHERE k.id_jemaah = $id_jemaah 
                          AND MONTH(s.tanggal_sumbangan) = MONTH(CURDATE()) 
                          AND YEAR(s.tanggal_sumbangan) = YEAR(CURDATE())";
$sumbangan_bulan_ini = mysqli_fetch_assoc(mysqli_query($koneksi, $query_sumbangan_bulan))['total'];

// Ambil riwayat kehadiran terbaru (10 terakhir)
$query_riwayat = "SELECT k.tanggal_hadir, k.waktu_hadir, s.jumlah as sumbangan 
                  FROM kehadiran k 
                  LEFT JOIN sumbangan s ON k.id = s.id_kehadiran 
                  WHERE k.id_jemaah = $id_jemaah 
                  ORDER BY k.tanggal_hadir DESC, k.waktu_hadir DESC 
                  LIMIT 10";
$result_riwayat = mysqli_query($koneksi, $query_riwayat);
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Profil Saya</h1>

    <div class="row">
        <!-- Informasi Profil -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Informasi Profil</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-person-fill text-muted" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="mt-2 mb-0"><?= htmlspecialchars($jemaah_data['nama']) ?></h5>
                        <small class="text-muted">Jemaah</small>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-sm-12">
                            <p class="mb-2"><strong>Nomor Registrasi:</strong></p>
                            <p class="text-muted mb-3"><?= $jemaah_data['nomor_registrasi'] ?></p>
                        </div>
                        <div class="col-sm-12">
                            <p class="mb-2"><strong>Jenis Kelamin:</strong></p>
                            <p class="text-muted mb-3"><?= $jemaah_data['jenis_kelamin'] ?></p>
                        </div>
                        <div class="col-sm-12">
                            <p class="mb-2"><strong>Bin/Binti:</strong></p>
                            <p class="text-muted mb-3"><?= htmlspecialchars($jemaah_data['bin_binti'] ?? '-') ?></p>
                        </div>
                        <div class="col-sm-12">
                            <p class="mb-2"><strong>Alamat:</strong></p>
                            <p class="text-muted mb-3"><?= htmlspecialchars($jemaah_data['alamat'] ?? '-') ?></p>
                        </div>
                        <div class="col-sm-12">
                            <p class="mb-2"><strong>Username:</strong></p>
                            <p class="text-muted mb-0"><?= htmlspecialchars($jemaah_data['username']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Jemaah -->
            <div class="card shadow mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Kartu Jemaah</h5>
                </div>
                <div class="card-body text-center">
                    <div id="barcode-container" class="mb-3"></div>
                    <p class="mb-2"><strong><?= htmlspecialchars($jemaah_data['nama']) ?></strong></p>
                    <p class="text-muted small"><?= $jemaah_data['nomor_registrasi'] ?></p>
                    <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Cetak Kartu
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistik dan Riwayat -->
        <div class="col-md-8">
            <!-- Statistik -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Kehadiran</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_kehadiran ?> kali</div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-calendar-check text-primary" style="font-size: 2rem; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Kehadiran Bulan Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $kehadiran_bulan_ini ?> kali</div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-calendar-month text-success" style="font-size: 2rem; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-3">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Sumbangan</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($total_sumbangan, 0, ',', '.') ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-cash-coin text-info" style="font-size: 2rem; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sumbangan Bulan Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($sumbangan_bulan_ini, 0, ',', '.') ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-wallet2 text-warning" style="font-size: 2rem; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Kehadiran -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Kehadiran Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Sumbangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result_riwayat) > 0): ?>
                                    <?php $no = 1; while ($riwayat = mysqli_fetch_assoc($result_riwayat)): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= date('d F Y', strtotime($riwayat['tanggal_hadir'])) ?></td>
                                            <td><?= $riwayat['waktu_hadir'] ?></td>
                                            <td class="text-end">
                                                <?php if ($riwayat['sumbangan']): ?>
                                                    Rp <?= number_format($riwayat['sumbangan'], 0, ',', '.') ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada riwayat kehadiran</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Generate barcode untuk kartu jemaah
document.addEventListener('DOMContentLoaded', function() {
    JsBarcode("#barcode-container", "<?= $jemaah_data['nomor_registrasi'] ?>", {
        format: "CODE128",
        lineColor: "#000",
        width: 2,
        height: 60,
        displayValue: false,
        background: "transparent"
    });
});
</script>

<style>
@media print {
    .container-fluid {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    
    .btn {
        display: none !important;
    }
}
</style>