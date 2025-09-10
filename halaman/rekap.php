<?php
// ===================================================================
// KODE UNTUK FILE: halaman/rekap.php (VERSI BARU DENGAN PAGINASI LEBIH BAIK)
// (Salin dan ganti seluruh isi file rekap.php Anda dengan kode ini)
// ===================================================================

// Cek akses - admin dan operator yang bisa mengakses halaman ini
requirePermission(['admin', 'operator']);

// --- PENGATURAN PAGINASI & FILTER ---
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$jenis_rekap = $_GET['jenis'] ?? 'kehadiran';
$periode = $_GET['periode'] ?? 'harian';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$search = $_GET['search'] ?? '';

// --- MEMBANGUN QUERY SECARA DINAMIS ---
$where_conditions = [];
$params = [];
$types = '';

// Filter berdasarkan periode
if ($periode == 'harian') {
    $where_conditions[] = ($jenis_rekap == 'kehadiran') ? "k.tanggal_hadir = ?" : "s.tanggal_sumbangan = ?";
    $params[] = $tanggal;
    $types .= 's';
} elseif ($periode == 'mingguan') {
    $start_of_week = date('Y-m-d', strtotime('monday this week', strtotime($tanggal)));
    $end_of_week = date('Y-m-d', strtotime('sunday this week', strtotime($tanggal)));
    $where_conditions[] = ($jenis_rekap == 'kehadiran') ? "k.tanggal_hadir BETWEEN ? AND ?" : "s.tanggal_sumbangan BETWEEN ? AND ?";
    $params[] = $start_of_week;
    $params[] = $end_of_week;
    $types .= 'ss';
} elseif ($periode == 'bulanan') {
    $where_conditions[] = ($jenis_rekap == 'kehadiran') ? "MONTH(k.tanggal_hadir) = ? AND YEAR(k.tanggal_hadir) = ?" : "MONTH(s.tanggal_sumbangan) = ? AND YEAR(s.tanggal_sumbangan) = ?";
    $params[] = $bulan;
    $params[] = $tahun;
    $types .= 'is';
}

// Filter berdasarkan pencarian
if (!empty($search)) {
    $where_conditions[] = "(j.nama LIKE ? OR j.nomor_registrasi LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$where_clause = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";

// --- QUERY UNTUK MENGHITUNG TOTAL DATA (UNTUK PAGINASI) ---
$count_query_base = ($jenis_rekap == 'kehadiran')
    ? "SELECT COUNT(k.id) as total FROM kehadiran k JOIN jemaah j ON k.id_jemaah = j.id"
    : "SELECT COUNT(s.id) as total FROM sumbangan s JOIN kehadiran k ON s.id_kehadiran = k.id JOIN jemaah j ON k.id_jemaah = j.id";

$count_stmt = $koneksi->prepare($count_query_base . " " . $where_clause);
if (count($params) > 0) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_results = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// --- QUERY UNTUK MENGAMBIL DATA SESUAI HALAMAN ---
$data_query_base = ($jenis_rekap == 'kehadiran')
    ? "SELECT j.nama, j.nomor_registrasi, k.tanggal_hadir, k.waktu_hadir FROM kehadiran k JOIN jemaah j ON k.id_jemaah = j.id"
    : "SELECT j.nama, j.nomor_registrasi, s.jumlah, s.tanggal_sumbangan FROM sumbangan s JOIN kehadiran k ON s.id_kehadiran = k.id JOIN jemaah j ON k.id_jemaah = j.id";

$data_query = $data_query_base . " " . $where_clause . " ORDER BY k.tanggal_hadir DESC, k.waktu_hadir DESC LIMIT ? OFFSET ?";
$data_types = $types . 'ii';
$data_params = array_merge($params, [$limit, $offset]);

$data_stmt = $koneksi->prepare($data_query);
if (count($data_params) > 0) {
    $data_stmt->bind_param($data_types, ...$data_params);
}
$data_stmt->execute();
$result = $data_stmt->get_result();
$data_rekap = [];
while ($row = $result->fetch_assoc()) {
    $data_rekap[] = $row;
}

// --- QUERY UNTUK TOTAL SUMBANGAN (HANYA JIKA REKAP SUMBANGAN) ---
$total_sumbangan = 0;
if ($jenis_rekap == 'sumbangan') {
    $sum_query = "SELECT SUM(s.jumlah) as total_sum FROM sumbangan s JOIN kehadiran k ON s.id_kehadiran = k.id JOIN jemaah j ON k.id_jemaah = j.id " . $where_clause;
    $sum_stmt = $koneksi->prepare($sum_query);
    if (count($params) > 0) {
        $sum_stmt->bind_param($types, ...$params);
    }
    $sum_stmt->execute();
    $total_sumbangan = $sum_stmt->get_result()->fetch_assoc()['total_sum'] ?? 0;
}
?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Rekapitulasi Data</h1>

    <!-- FORM FILTER -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="halaman" value="rekap">
                <div class="row g-3 align-items-end">
                    <!-- Jenis & Periode -->
                    <div class="col-md-2"><label class="form-label">Jenis Rekap</label><select name="jenis" class="form-select"><option value="kehadiran" <?= $jenis_rekap == 'kehadiran' ? 'selected' : '' ?>>Kehadiran</option><option value="sumbangan" <?= $jenis_rekap == 'sumbangan' ? 'selected' : '' ?>>Sumbangan</option></select></div>
                    <div class="col-md-2"><label class="form-label">Periode</label><select name="periode" id="periode" class="form-select" onchange="toggleDateInputs(this.value)"><option value="harian" <?= $periode == 'harian' ? 'selected' : '' ?>>Harian</option><option value="mingguan" <?= $periode == 'mingguan' ? 'selected' : '' ?>>Mingguan</option><option value="bulanan" <?= $periode == 'bulanan' ? 'selected' : '' ?>>Bulanan</option></select></div>
                    
                    <!-- Input Tanggal/Bulan -->
                    <div class="col-md-3">
                        <div id="input-harian-mingguan"><label class="form-label">Pilih Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>"></div>
                        <div id="input-bulanan" class="d-none row g-2">
                            <div class="col-7"><label class="form-label">Bulan</label><select name="bulan" class="form-select"><?php for ($i = 1; $i <= 12; $i++): ?><option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $i, 10)) ?></option><?php endfor; ?></select></div>
                            <div class="col-5"><label class="form-label">Tahun</label><input type="number" name="tahun" class="form-control" value="<?= htmlspecialchars($tahun) ?>"></div>
                        </div>
                    </div>

                    <!-- Input Pencarian -->
                    <div class="col-md-3">
                        <label class="form-label">Cari Nama/No. Reg</label>
                        <input type="text" name="search" class="form-control" placeholder="Ketik di sini..." value="<?= htmlspecialchars($search) ?>">
                    </div>

                    <!-- Tombol -->
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-2"></i>Filter</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Hasil Rekapitulasi</h5>
            <span class="badge bg-info">Menampilkan <?= count($data_rekap) ?> dari <?= $total_results ?> data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <?php if ($jenis_rekap == 'kehadiran'): ?>
                        <tr><th>No</th><th>Nama</th><th>No. Registrasi</th><th>Tanggal Hadir</th><th>Waktu Hadir</th></tr>
                        <?php else: ?>
                        <tr><th>No</th><th>Nama</th><th>No. Registrasi</th><th>Tanggal Sumbangan</th><th class="text-end">Jumlah (Rp)</th></tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php if (empty($data_rekap)): ?>
                        <tr><td colspan="5" class="text-center">Tidak ada data yang cocok dengan filter Anda.</td></tr>
                        <?php else: $no = $offset + 1; foreach ($data_rekap as $data): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($data['nama']) ?></td>
                            <td><span class="badge bg-secondary"><?= $data['nomor_registrasi'] ?></span></td>
                            <?php if ($jenis_rekap == 'kehadiran'): ?>
                            <td><?= date('d F Y', strtotime($data['tanggal_hadir'])) ?></td>
                            <td><?= $data['waktu_hadir'] ?></td>
                            <?php else: ?>
                            <td><?= date('d F Y', strtotime($data['tanggal_sumbangan'])) ?></td>
                            <td class="text-end"><?= number_format($data['jumlah'], 0, ',', '.') ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                    <?php if ($jenis_rekap == 'sumbangan' && $total_sumbangan > 0): ?>
                    <tfoot class="fw-bold table-light">
                        <tr>
                            <td colspan="4" class="text-center">TOTAL SUMBANGAN (BERDASARKAN FILTER)</td>
                            <td class="text-end">Rp <?= number_format($total_sumbangan, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        
        <!-- NAVIGASI PAGINASI (VERSI BARU) -->
        <?php if ($total_pages > 1): ?>
        <div class="card-footer">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <?php
                    $query_params = $_GET;
                    
                    // Tombol 'Sebelumnya'
                    if ($page > 1) {
                        $prev_page = $page - 1;
                        $query_params['page'] = $prev_page;
                        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($query_params) . '">&laquo; Sebelumnya</a></li>';
                    } else {
                        echo '<li class="page-item disabled"><span class="page-link">&laquo; Sebelumnya</span></li>';
                    }

                    // Logika untuk menampilkan nomor halaman
                    $range = 1; // Jumlah halaman di sekitar halaman aktif
                    $show_ellipsis = false;
                    for ($i = 1; $i <= $total_pages; $i++) {
                        if ($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)) {
                            $query_params['page'] = $i;
                            $active_class = ($i == $page) ? 'active' : '';
                            echo '<li class="page-item ' . $active_class . '"><a class="page-link" href="?' . http_build_query($query_params) . '">' . $i . '</a></li>';
                            $show_ellipsis = true;
                        } elseif ($show_ellipsis) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            $show_ellipsis = false;
                        }
                    }

                    // Tombol 'Berikutnya'
                    if ($page < $total_pages) {
                        $next_page = $page + 1;
                        $query_params['page'] = $next_page;
                        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($query_params) . '">Berikutnya &raquo;</a></li>';
                    } else {
                        echo '<li class="page-item disabled"><span class="page-link">Berikutnya &raquo;</span></li>';
                    }
                    ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleDateInputs(periode) {
    const harianMingguan = document.getElementById('input-harian-mingguan');
    const bulanan = document.getElementById('input-bulanan');
    
    if (periode === 'bulanan') {
        harianMingguan.classList.add('d-none');
        bulanan.classList.remove('d-none');
    } else {
        harianMingguan.classList.remove('d-none');
        bulanan.classList.add('d-none');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const periodeSelect = document.getElementById('periode');
    if (periodeSelect) {
        toggleDateInputs(periodeSelect.value);
    }
});
</script>
