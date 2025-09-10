<?php
// ===================================================================
// HALAMAN KELOLA KEHADIRAN (KHUSUS ADMIN)
// ===================================================================

// Cek akses - hanya admin yang bisa mengakses halaman ini
requirePermission(['admin']);

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter berdasarkan tanggal
$tanggal_filter = $_GET['tanggal'] ?? date('Y-m-d');
$search = $_GET['search'] ?? '';

// Build query conditions
$where_conditions = [];
$params = [];
$types = '';

if (!empty($tanggal_filter)) {
    $where_conditions[] = "k.tanggal_hadir = ?";
    $params[] = $tanggal_filter;
    $types .= 's';
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
$count_query = "SELECT COUNT(k.id) as total FROM kehadiran k 
                JOIN jemaah j ON k.id_jemaah = j.id $where_clause";
$count_stmt = $koneksi->prepare($count_query);
if (count($params) > 0) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_results = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// Get attendance records
$data_query = "SELECT k.*, j.nama, j.nomor_registrasi, 
               COALESCE(s.jumlah, 0) as sumbangan
               FROM kehadiran k 
               JOIN jemaah j ON k.id_jemaah = j.id 
               LEFT JOIN sumbangan s ON k.id = s.id_kehadiran 
               $where_clause 
               ORDER BY k.tanggal_hadir DESC, k.waktu_hadir DESC 
               LIMIT ? OFFSET ?";

$data_types = $types . 'ii';
$data_params = array_merge($params, [$limit, $offset]);

$data_stmt = $koneksi->prepare($data_query);
if (count($data_params) > 0) {
    $data_stmt->bind_param($data_types, ...$data_params);
}
$data_stmt->execute();
$result_kehadiran = $data_stmt->get_result();

// Get all jemaah for dropdown
$query_jemaah = "SELECT id, nama, nomor_registrasi FROM jemaah ORDER BY nama";
$result_jemaah = mysqli_query($koneksi, $query_jemaah);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Kelola Kehadiran</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahKehadiranModal">
            <i class="bi bi-plus-circle-fill me-2"></i>Tambah Kehadiran
        </button>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="halaman" value="kehadiran">
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal_filter) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cari Nama/No. Registrasi</label>
                    <input type="text" name="search" class="form-control" placeholder="Ketik di sini..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-search me-2"></i>Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="index.php?halaman=kehadiran" class="btn btn-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Card -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?= !empty($tanggal_filter) ? 'Kehadiran ' . date('d F Y', strtotime($tanggal_filter)) : 'Total Kehadiran' ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_results ?> orang</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people-fill text-primary" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
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
            ?>
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?= !empty($tanggal_filter) ? 'Sumbangan ' . date('d F Y', strtotime($tanggal_filter)) : 'Total Sumbangan' ?>
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
    </div>

    <!-- Attendance Table -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Kehadiran</h5>
            <span class="badge bg-info">Menampilkan <?= mysqli_num_rows($result_kehadiran) ?> dari <?= $total_results ?> data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Nama Jemaah</th>
                            <th>No. Registrasi</th>
                            <th>Sumbangan</th>
                            <th width="150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_kehadiran) > 0): ?>
                            <?php $no = $offset + 1; while ($kehadiran = mysqli_fetch_assoc($result_kehadiran)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= date('d/m/Y', strtotime($kehadiran['tanggal_hadir'])) ?></td>
                                    <td><?= $kehadiran['waktu_hadir'] ?></td>
                                    <td><?= htmlspecialchars($kehadiran['nama']) ?></td>
                                    <td><span class="badge bg-secondary"><?= $kehadiran['nomor_registrasi'] ?></span></td>
                                    <td class="text-end">
                                        <?php if ($kehadiran['sumbangan'] > 0): ?>
                                            Rp <?= number_format($kehadiran['sumbangan'], 0, ',', '.') ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="editKehadiran(<?= $kehadiran['id'] ?>, '<?= $kehadiran['tanggal_hadir'] ?>', '<?= $kehadiran['waktu_hadir'] ?>', <?= $kehadiran['id_jemaah'] ?>, <?= $kehadiran['sumbangan'] ?>)" 
                                                    title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="hapusKehadiran(<?= $kehadiran['id'] ?>, '<?= htmlspecialchars(addslashes($kehadiran['nama'])) ?>', '<?= $kehadiran['tanggal_hadir'] ?>')" 
                                                    title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Tidak ada data kehadiran</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="card-footer">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <?php
                    $query_params = $_GET;
                    
                    // Previous button
                    if ($page > 1) {
                        $prev_page = $page - 1;
                        $query_params['page'] = $prev_page;
                        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($query_params) . '">&laquo; Sebelumnya</a></li>';
                    } else {
                        echo '<li class="page-item disabled"><span class="page-link">&laquo; Sebelumnya</span></li>';
                    }

                    // Page numbers
                    $range = 1;
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

                    // Next button
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

<!-- Modal Tambah Kehadiran -->
<div class="modal fade" id="tambahKehadiranModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kehadiran Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="scan-barcode" class="form-label">Scan Barcode / No. Registrasi</label>
                        <input type="text" class="form-control form-control-lg" id="scan-barcode" name="nomor_registrasi" 
                               placeholder="Scan barcode atau ketik nomor registrasi" autofocus required>
                        <small class="text-muted">Fokuskan kursor di sini dan scan barcode</small>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal-hadir" class="form-label">Tanggal Hadir</label>
                        <input type="date" class="form-control" id="tanggal-hadir" name="tanggal_hadir" 
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="waktu-hadir" class="form-label">Waktu Hadir</label>
                        <input type="time" class="form-control" id="waktu-hadir" name="waktu_hadir" 
                               value="<?= date('H:i') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="sumbangan" class="form-label">Sumbangan (Rp)</label>
                        <input type="number" class="form-control" id="sumbangan" name="jumlah_sumbangan" 
                               value="1000" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_kehadiran_manual" class="btn btn-primary">Simpan Kehadiran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kehadiran -->
<div class="modal fade" id="editKehadiranModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kehadiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php">
                <input type="hidden" id="edit-id-kehadiran" name="id_kehadiran">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-jemaah" class="form-label">Jemaah</label>
                        <select class="form-select" id="edit-jemaah" name="id_jemaah" required>
                            <option value="">Pilih Jemaah</option>
                            <?php mysqli_data_seek($result_jemaah, 0); while ($jemaah = mysqli_fetch_assoc($result_jemaah)): ?>
                                <option value="<?= $jemaah['id'] ?>"><?= htmlspecialchars($jemaah['nama']) ?> (<?= $jemaah['nomor_registrasi'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-tanggal" class="form-label">Tanggal Hadir</label>
                        <input type="date" class="form-control" id="edit-tanggal" name="tanggal_hadir" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-waktu" class="form-label">Waktu Hadir</label>
                        <input type="time" class="form-control" id="edit-waktu" name="waktu_hadir" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-sumbangan" class="form-label">Sumbangan (Rp)</label>
                        <input type="number" class="form-control" id="edit-sumbangan" name="jumlah_sumbangan" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_kehadiran" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Kehadiran -->
<div class="modal fade" id="hapusKehadiranModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php">
                <input type="hidden" id="hapus-id-kehadiran" name="id_kehadiran">
                <div class="modal-body">
                    <div class="text-center">
                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Yakin ingin menghapus data kehadiran?</h5>
                        <p class="mb-0">Jemaah: <strong id="hapus-nama-jemaah"></strong></p>
                        <p class="mb-0">Tanggal: <strong id="hapus-tanggal"></strong></p>
                        <p class="text-muted small mt-2">Data yang sudah dihapus tidak dapat dikembalikan!</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="hapus_kehadiran" class="btn btn-danger">Ya, Hapus Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit kehadiran function
function editKehadiran(id, tanggal, waktu, idJemaah, sumbangan) {
    document.getElementById('edit-id-kehadiran').value = id;
    document.getElementById('edit-jemaah').value = idJemaah;
    document.getElementById('edit-tanggal').value = tanggal;
    document.getElementById('edit-waktu').value = waktu;
    document.getElementById('edit-sumbangan').value = sumbangan;
    
    var modal = new bootstrap.Modal(document.getElementById('editKehadiranModal'));
    modal.show();
}

// Hapus kehadiran function
function hapusKehadiran(id, nama, tanggal) {
    document.getElementById('hapus-id-kehadiran').value = id;
    document.getElementById('hapus-nama-jemaah').textContent = nama;
    document.getElementById('hapus-tanggal').textContent = new Date(tanggal).toLocaleDateString('id-ID');
    
    var modal = new bootstrap.Modal(document.getElementById('hapusKehadiranModal'));
    modal.show();
}

// Auto-submit when barcode is scanned (when Enter is pressed)
document.getElementById('scan-barcode').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        // Find the form and submit it
        this.closest('form').querySelector('button[type="submit"]').click();
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