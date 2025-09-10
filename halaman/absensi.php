<?php
// Cek akses - hanya admin dan operator yang bisa mengakses halaman ini
requirePermission(['admin', 'operator']);
?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card text-center">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Absensi Kehadiran</h4>
                </div>
                <div class="card-body p-4">
                    <i class="bi bi-qr-code-scan display-1 text-success mb-3"></i>
                    <h5 class="card-title">Scan Barcode & Input Sumbangan</h5>
                    <p class="text-muted">Scan barcode, sesuaikan sumbangan jika perlu, lalu tekan Enter atau klik tombol.</p>
                    
                    <?php
                    if (isset($_SESSION['notifikasi_absensi'])) {
                        echo '<div class="alert alert-' . $_SESSION['notifikasi_absensi']['jenis'] . ' mt-3" role="alert">' . $_SESSION['notifikasi_absensi']['pesan'] . '</div>';
                        unset($_SESSION['notifikasi_absensi']);
                    }
                    ?>
                    
                    <form action="index.php" method="POST" class="mt-4">
                        <div class="input-group mb-3">
                           <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                           <input type="text" name="nomor_registrasi" class="form-control form-control-lg" placeholder="Menunggu scan..." autofocus required>
                        </div>
                        
                        <div class="input-group mb-3">
                           <span class="input-group-text fw-bold">Rp</span>
                           <input type="number" name="jumlah_sumbangan" class="form-control form-control-lg" value="1000" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="scan_barcode" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Catat Kehadiran
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-muted">Hari Ini: <?= date('d F Y') ?></div>
            </div>
        </div>
    </div>
</div>