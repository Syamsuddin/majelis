<?php
// ===================================================================
// HALAMAN KELOLA SUMBANGAN (KHUSUS ADMIN)
// ===================================================================

// Cek akses - hanya admin yang bisa mengakses halaman ini
requirePermission(['admin']);

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter berdasarkan tanggal
$tanggal_filter = $_GET['tanggal'] ?? '';
$search = $_GET['search'] ?? '';
$periode_filter = $_GET['periode'] ?? '';  // Tambah filter periode

// Build query conditions
$where_conditions = [];
$params = [];
$types = '';

if (!empty($tanggal_filter)) {
    $where_conditions[] = "s.tanggal_sumbangan = ?";
    $params[] = $tanggal_filter;
    $types .= 's';
}

// Filter berdasarkan periode (mingguan, bulanan, tahunan)
if (!empty($periode_filter)) {
    switch ($periode_filter) {
        case 'minggu_ini':
            $where_conditions[] = "YEARWEEK(s.tanggal_sumbangan) = YEARWEEK(CURDATE())";
            break;
        case 'bulan_ini':
            $where_conditions[] = "MONTH(s.tanggal_sumbangan) = MONTH(CURDATE()) AND YEAR(s.tanggal_sumbangan) = YEAR(CURDATE())";
            break;
        case 'tahun_ini':
            $where_conditions[] = "YEAR(s.tanggal_sumbangan) = YEAR(CURDATE())";
            break;
        case 'minggu_lalu':
            $where_conditions[] = "YEARWEEK(s.tanggal_sumbangan) = YEARWEEK(CURDATE()) - 1";
            break;
        case 'bulan_lalu':
            $where_conditions[] = "MONTH(s.tanggal_sumbangan) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(s.tanggal_sumbangan) = YEAR(CURDATE() - INTERVAL 1 MONTH)";
            break;
        case 'tahun_lalu':
            $where_conditions[] = "YEAR(s.tanggal_sumbangan) = YEAR(CURDATE()) - 1";
            break;
    }
}

if (!empty($search)) {
    $where_conditions[] = "(j.nama LIKE ? OR j.nomor_registrasi LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$where_clause = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Count total records for pagination
$count_query = "SELECT COUNT(s.id) as total FROM sumbangan s 
                JOIN kehadiran k ON s.id_kehadiran = k.id 
                JOIN jemaah j ON k.id_jemaah = j.id $where_clause";
$count_stmt = $koneksi->prepare($count_query);
if (count($params) > 0) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_results = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// Get donation records
$data_query = "SELECT s.*, j.nama, j.nomor_registrasi, k.tanggal_hadir, k.waktu_hadir
               FROM sumbangan s 
               JOIN kehadiran k ON s.id_kehadiran = k.id 
               JOIN jemaah j ON k.id_jemaah = j.id 
               $where_clause 
               ORDER BY s.tanggal_sumbangan DESC, k.waktu_hadir DESC 
               LIMIT ? OFFSET ?";

$data_types = $types . 'ii';
$data_params = array_merge($params, [$limit, $offset]);

$data_stmt = $koneksi->prepare($data_query);
if (count($data_params) > 0) {
    $data_stmt->bind_param($data_types, ...$data_params);
}
$data_stmt->execute();
$result_sumbangan = $data_stmt->get_result();

// Get all jemaah for dropdown
$query_jemaah = "SELECT id, nama, nomor_registrasi FROM jemaah ORDER BY nama";
$result_jemaah = mysqli_query($koneksi, $query_jemaah);

// Get all kehadiran for dropdown
$query_kehadiran = "SELECT k.id, j.nama, k.tanggal_hadir, k.waktu_hadir 
                    FROM kehadiran k 
                    JOIN jemaah j ON k.id_jemaah = j.id 
                    ORDER BY k.tanggal_hadir DESC, k.waktu_hadir DESC";
$result_kehadiran = mysqli_query($koneksi, $query_kehadiran);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Kelola Sumbangan</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahSumbanganModal">
            <i class="bi bi-plus-circle-fill me-2"></i>Tambah Sumbangan
        </button>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Filter Sumbangan</h6>
            <?php if (!empty($periode_filter) || !empty($tanggal_filter) || !empty($search)): ?>
                <span class="badge bg-success">Filter Aktif</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (!empty($periode_filter) || !empty($tanggal_filter) || !empty($search)): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Filter Aktif:</strong>
                    <?php if (!empty($periode_filter)): ?>
                        <span class="badge bg-primary me-1">
                            <?php
                            $periode_labels = [
                                'minggu_ini' => 'Minggu Ini',
                                'bulan_ini' => 'Bulan Ini', 
                                'tahun_ini' => 'Tahun Ini',
                                'minggu_lalu' => 'Minggu Lalu',
                                'bulan_lalu' => 'Bulan Lalu',
                                'tahun_lalu' => 'Tahun Lalu'
                            ];
                            echo $periode_labels[$periode_filter] ?? $periode_filter;
                            ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($tanggal_filter)): ?>
                        <span class="badge bg-secondary me-1">Tanggal: <?= date('d/m/Y', strtotime($tanggal_filter)) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($search)): ?>
                        <span class="badge bg-warning me-1">Pencarian: "<?= htmlspecialchars($search) ?>"</span>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <form method="GET" class="row g-3">
                <input type="hidden" name="halaman" value="sumbangan">
                
                <!-- Filter Periode -->
                <div class="col-md-3">
                    <label class="form-label">Filter Periode</label>
                    <select name="periode" class="form-select">
                        <option value="">Semua Periode</option>
                        <option value="minggu_ini" <?= $periode_filter == 'minggu_ini' ? 'selected' : '' ?>>Minggu Ini</option>
                        <option value="bulan_ini" <?= $periode_filter == 'bulan_ini' ? 'selected' : '' ?>>Bulan Ini</option>
                        <option value="tahun_ini" <?= $periode_filter == 'tahun_ini' ? 'selected' : '' ?>>Tahun Ini</option>
                        <option value="minggu_lalu" <?= $periode_filter == 'minggu_lalu' ? 'selected' : '' ?>>Minggu Lalu</option>
                        <option value="bulan_lalu" <?= $periode_filter == 'bulan_lalu' ? 'selected' : '' ?>>Bulan Lalu</option>
                        <option value="tahun_lalu" <?= $periode_filter == 'tahun_lalu' ? 'selected' : '' ?>>Tahun Lalu</option>
                    </select>
                </div>
                
                <!-- Filter Tanggal Spesifik -->
                <div class="col-md-3">
                    <label class="form-label">Tanggal Spesifik</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal_filter) ?>">
                </div>
                
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label">Cari Nama/No. Registrasi</label>
                    <input type="text" name="search" class="form-control" placeholder="Ketik di sini..." value="<?= htmlspecialchars($search) ?>">
                </div>
                
                <!-- Action Buttons -->
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-search me-2"></i>Filter
                        </button>
                        <a href="index.php?halaman=sumbangan" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-4 mb-4">
            <?php
            $total_sumbangan_query = "SELECT COALESCE(SUM(s.jumlah), 0) as total 
                                     FROM sumbangan s 
                                     JOIN kehadiran k ON s.id_kehadiran = k.id 
                                     JOIN jemaah j ON k.id_jemaah = j.id 
                                     $where_clause";
            $sumbangan_stmt = $koneksi->prepare($total_sumbangan_query);
            if (count($params) > 0) {
                $sumbangan_stmt->bind_param($types, ...$params);
            }
            $sumbangan_stmt->execute();
            $total_sumbangan = $sumbangan_stmt->get_result()->fetch_assoc()['total'];
            
            // Determine title based on filter
            $title = 'Total Sumbangan';
            if (!empty($periode_filter)) {
                switch ($periode_filter) {
                    case 'minggu_ini': $title = 'Sumbangan Minggu Ini'; break;
                    case 'bulan_ini': $title = 'Sumbangan Bulan Ini'; break;
                    case 'tahun_ini': $title = 'Sumbangan Tahun Ini'; break;
                    case 'minggu_lalu': $title = 'Sumbangan Minggu Lalu'; break;
                    case 'bulan_lalu': $title = 'Sumbangan Bulan Lalu'; break;
                    case 'tahun_lalu': $title = 'Sumbangan Tahun Lalu'; break;
                }
            } elseif (!empty($tanggal_filter)) {
                $title = 'Sumbangan ' . date('d F Y', strtotime($tanggal_filter));
            }
            ?>
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?= $title ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($total_sumbangan, 0, ',', '.') ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-wallet2 text-success" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?= !empty($periode_filter) || !empty($tanggal_filter) ? 'Jumlah Record (Filter)' : 'Total Record' ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_results, 0, ',', '.') ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-list-check text-primary" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-4 mb-4">
            <?php
            // Get average donation amount for the filtered period
            $avg_sumbangan_query = "SELECT COALESCE(AVG(s.jumlah), 0) as rata_rata 
                                   FROM sumbangan s 
                                   JOIN kehadiran k ON s.id_kehadiran = k.id 
                                   JOIN jemaah j ON k.id_jemaah = j.id 
                                   $where_clause";
            $avg_stmt = $koneksi->prepare($avg_sumbangan_query);
            if (count($params) > 0) {
                $avg_stmt->bind_param($types, ...$params);
            }
            $avg_stmt->execute();
            $rata_rata_sumbangan = $avg_stmt->get_result()->fetch_assoc()['rata_rata'];
            ?>
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Rata-rata Sumbangan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($rata_rata_sumbangan, 0, ',', '.') ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calculator text-warning" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Sumbangan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Jemaah</th>
                            <th>No. Registrasi</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_sumbangan) > 0): ?>
                            <?php $no = $offset + 1; while ($sumbangan = mysqli_fetch_assoc($result_sumbangan)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y', strtotime($sumbangan['tanggal_sumbangan'])) ?></td>
                            <td><?= htmlspecialchars($sumbangan['nama']) ?></td>
                            <td><span class="badge bg-secondary"><?= $sumbangan['nomor_registrasi'] ?></span></td>
                            <td class="text-end">
                                <strong>Rp <?= number_format($sumbangan['jumlah'], 0, ',', '.') ?></strong>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-warning" 
                                            onclick="editSumbangan(<?= $sumbangan['id'] ?>, '<?= $sumbangan['tanggal_sumbangan'] ?>', <?= $sumbangan['id_kehadiran'] ?>, <?= $sumbangan['jumlah'] ?>)" 
                                            title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="hapusSumbangan(<?= $sumbangan['id'] ?>, '<?= htmlspecialchars($sumbangan['nama']) ?>', '<?= $sumbangan['tanggal_sumbangan'] ?>')" 
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data sumbangan</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?halaman=sumbangan&page=<?= $page - 1 ?>&periode=<?= urlencode($periode_filter) ?>&tanggal=<?= urlencode($tanggal_filter) ?>&search=<?= urlencode($search) ?>">Previous</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?halaman=sumbangan&page=<?= $i ?>&periode=<?= urlencode($periode_filter) ?>&tanggal=<?= urlencode($tanggal_filter) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?halaman=sumbangan&page=<?= $page + 1 ?>&periode=<?= urlencode($periode_filter) ?>&tanggal=<?= urlencode($tanggal_filter) ?>&search=<?= urlencode($search) ?>">Next</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Sumbangan -->
<div class="modal fade" id="tambahSumbanganModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Sumbangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kehadiran" class="form-label">Pilih Kehadiran</label>
                        <select class="form-select" id="kehadiran" name="id_kehadiran" required>
                            <option value="">Pilih Kehadiran</option>
                            <?php mysqli_data_seek($result_kehadiran, 0); while ($kehadiran = mysqli_fetch_assoc($result_kehadiran)): ?>
                                <option value="<?= $kehadiran['id'] ?>">
                                    <?= htmlspecialchars($kehadiran['nama']) ?> - <?= date('d/m/Y', strtotime($kehadiran['tanggal_hadir'])) ?> (<?= $kehadiran['waktu_hadir'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal-sumbangan" class="form-label">Tanggal Sumbangan</label>
                        <input type="date" class="form-control" id="tanggal-sumbangan" name="tanggal_sumbangan" 
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="jumlah-sumbangan" class="form-label">Jumlah Sumbangan (Rp)</label>
                        <input type="number" class="form-control" id="jumlah-sumbangan" name="jumlah_sumbangan" 
                               value="1000" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_sumbangan" class="btn btn-primary">Simpan Sumbangan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Sumbangan -->
<div class="modal fade" id="editSumbanganModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Sumbangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php">
                <input type="hidden" id="edit-id-sumbangan" name="id_sumbangan">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-kehadiran" class="form-label">Pilih Kehadiran</label>
                        <select class="form-select" id="edit-kehadiran" name="id_kehadiran" required>
                            <option value="">Pilih Kehadiran</option>
                            <?php mysqli_data_seek($result_kehadiran, 0); while ($kehadiran = mysqli_fetch_assoc($result_kehadiran)): ?>
                                <option value="<?= $kehadiran['id'] ?>">
                                    <?= htmlspecialchars($kehadiran['nama']) ?> - <?= date('d/m/Y', strtotime($kehadiran['tanggal_hadir'])) ?> (<?= $kehadiran['waktu_hadir'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-tanggal-sumbangan" class="form-label">Tanggal Sumbangan</label>
                        <input type="date" class="form-control" id="edit-tanggal-sumbangan" name="tanggal_sumbangan" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-jumlah-sumbangan" class="form-label">Jumlah Sumbangan (Rp)</label>
                        <input type="number" class="form-control" id="edit-jumlah-sumbangan" name="jumlah_sumbangan" 
                               min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_sumbangan" class="btn btn-warning">Update Sumbangan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Sumbangan -->
<div class="modal fade" id="hapusSumbanganModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php">
                <input type="hidden" id="hapus-id-sumbangan" name="id_sumbangan">
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data sumbangan ini?</p>
                    <div class="alert alert-warning">
                        <strong>Nama:</strong> <span id="hapus-nama-jemaah"></span><br>
                        <strong>Tanggal:</strong> <span id="hapus-tanggal-sumbangan"></span>
                    </div>
                    <p class="text-danger"><strong>Peringatan:</strong> Data yang sudah dihapus tidak dapat dikembalikan!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="hapus_sumbangan" class="btn btn-danger">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit sumbangan function
function editSumbangan(id, tanggal, idKehadiran, jumlah) {
    document.getElementById('edit-id-sumbangan').value = id;
    document.getElementById('edit-kehadiran').value = idKehadiran;
    document.getElementById('edit-tanggal-sumbangan').value = tanggal;
    document.getElementById('edit-jumlah-sumbangan').value = jumlah;
    
    var modal = new bootstrap.Modal(document.getElementById('editSumbanganModal'));
    modal.show();
}

// Hapus sumbangan function
function hapusSumbangan(id, nama, tanggal) {
    document.getElementById('hapus-id-sumbangan').value = id;
    document.getElementById('hapus-nama-jemaah').textContent = nama;
    document.getElementById('hapus-tanggal-sumbangan').textContent = new Date(tanggal).toLocaleDateString('id-ID');
    
    var modal = new bootstrap.Modal(document.getElementById('hapusSumbanganModal'));
    modal.show();
}

// Auto-submit form when period filter changes
document.addEventListener('DOMContentLoaded', function() {
    const periodeSelect = document.querySelector('select[name="periode"]');
    if (periodeSelect) {
        periodeSelect.addEventListener('change', function() {
            // Clear specific date when period is selected
            const tanggalInput = document.querySelector('input[name="tanggal"]');
            if (this.value !== '' && tanggalInput) {
                tanggalInput.value = '';
            }
            // Submit form automatically
            this.closest('form').submit();
        });
    }
    
    // Clear period when specific date is selected
    const tanggalInput = document.querySelector('input[name="tanggal"]');
    if (tanggalInput) {
        tanggalInput.addEventListener('change', function() {
            if (this.value !== '' && periodeSelect) {
                periodeSelect.value = '';
            }
        });
    }
});
</script>

<style>
.btn-group .btn {
    margin-right: 2px;
}
.btn-group .btn:last-child {
    margin-right: 0;
}
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
</style>