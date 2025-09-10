<?php
// ===================================================================
// HALAMAN KELOLA USER (KHUSUS ADMIN)
// ===================================================================

// Cek akses admin
requirePermission(['admin']);

// Ambil data users
$query_users = "SELECT u.*, j.nama as nama_jemaah, j.nomor_registrasi 
                FROM users u 
                LEFT JOIN jemaah j ON u.id_jemaah = j.id 
                ORDER BY u.created_at DESC";
$result_users = mysqli_query($koneksi, $query_users);

// Ambil data jemaah untuk dropdown
$query_jemaah = "SELECT j.* FROM jemaah j 
                 LEFT JOIN users u ON j.id = u.id_jemaah 
                 WHERE u.id_jemaah IS NULL 
                 ORDER BY j.nama";
$result_jemaah = mysqli_query($koneksi, $query_jemaah);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Kelola User</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="bi bi-person-plus-fill me-2"></i>Tambah User
        </button>
    </div>

    <!-- Statistik Users -->
    <div class="row mb-4">
        <?php
        $stats_admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM users WHERE user_level = 'admin' AND status = 'active'"))['count'];
        $stats_operator = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM users WHERE user_level = 'operator' AND status = 'active'"))['count'];
        $stats_jemaah = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM users WHERE user_level = 'jemaah' AND status = 'active'"))['count'];
        ?>
        <div class="col-md-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Admin</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats_admin ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-shield-check text-primary" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Operator</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats_operator ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-gear text-success" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Jemaah</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats_jemaah ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people text-info" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Users -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar User</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Level</th>
                            <th>Jemaah Terkait</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_users) > 0): ?>
                            <?php $no = 1; while ($user = mysqli_fetch_assoc($result_users)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                                    <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                                    <td>
                                        <?php
                                        $badge_class = match($user['user_level']) {
                                            'admin' => 'bg-danger',
                                            'operator' => 'bg-warning',
                                            'jemaah' => 'bg-info',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= ucfirst($user['user_level']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($user['nama_jemaah']): ?>
                                            <?= htmlspecialchars($user['nama_jemaah']) ?><br>
                                            <small class="text-muted"><?= $user['nomor_registrasi'] ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $user['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $user['status'] == 'active' ? 'Aktif' : 'Nonaktif' ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-info" 
                                                    onclick="editUser(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['username'])) ?>', '<?= htmlspecialchars(addslashes($user['nama_lengkap'])) ?>', '<?= $user['user_level'] ?>', <?= $user['id_jemaah'] ?? 'null' ?>)" 
                                                    title="Edit User">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="resetPassword(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['nama_lengkap'])) ?>')" 
                                                    title="Reset Password">
                                                <i class="bi bi-key-fill"></i>
                                            </button>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $user['status'] == 'active' ? 'inactive' : 'active' ?>">
                                                <button type="submit" name="update_status" 
                                                        class="btn btn-sm <?= $user['status'] == 'active' ? 'btn-secondary' : 'btn-success' ?>"
                                                        onclick="return confirm('Yakin ingin mengubah status user ini?')" title="<?= $user['status'] == 'active' ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                    <i class="bi bi-<?= $user['status'] == 'active' ? 'pause' : 'play' ?>-fill"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="hapusUser(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['nama_lengkap'])) ?>', '<?= $user['user_level'] ?>')" 
                                                    title="Hapus User">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data user</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambahUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label for="user_level" class="form-label">Level User</label>
                        <select class="form-select" id="user_level" name="user_level" required onchange="toggleJemaahSelect(this.value)">
                            <option value="">Pilih Level</option>
                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                            <option value="jemaah">Jemaah</option>
                        </select>
                    </div>
                    <div class="mb-3" id="jemaah-select" style="display: none;">
                        <label for="id_jemaah" class="form-label">Pilih Jemaah</label>
                        <select class="form-select" id="id_jemaah" name="id_jemaah">
                            <option value="">Pilih Jemaah</option>
                            <?php mysqli_data_seek($result_jemaah, 0); while ($jemaah = mysqli_fetch_assoc($result_jemaah)): ?>
                                <option value="<?= $jemaah['id'] ?>"><?= htmlspecialchars($jemaah['nama']) ?> (<?= $jemaah['nomor_registrasi'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_user" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="modalEditUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" id="edit-user-id" name="user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit-username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-nama-lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="edit-nama-lengkap" name="nama_lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-user-level" class="form-label">Level User</label>
                        <select class="form-select" id="edit-user-level" name="user_level" required onchange="toggleEditJemaahSelect(this.value)">
                            <option value="">Pilih Level</option>
                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                            <option value="jemaah">Jemaah</option>
                        </select>
                    </div>
                    <div class="mb-3" id="edit-jemaah-select" style="display: none;">
                        <label for="edit-id-jemaah" class="form-label">Pilih Jemaah</label>
                        <select class="form-select" id="edit-id-jemaah" name="id_jemaah">
                            <option value="">Pilih Jemaah</option>
                            <?php 
                            // Get all jemaah for edit modal (including currently linked ones)
                            $query_all_jemaah = "SELECT * FROM jemaah ORDER BY nama";
                            $result_all_jemaah = mysqli_query($koneksi, $query_all_jemaah);
                            while ($jemaah = mysqli_fetch_assoc($result_all_jemaah)): 
                            ?>
                                <option value="<?= $jemaah['id'] ?>"><?= htmlspecialchars($jemaah['nama']) ?> (<?= $jemaah['nomor_registrasi'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_user" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="modalResetPassword" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark">
                    <i class="bi bi-key-fill me-2"></i>Reset Password User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" id="reset-user-id" name="user_id">
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Reset password untuk:</strong> <span id="reset-user-nama"></span>
                    </div>
                    <div class="mb-3">
                        <label for="new-password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="new-password" name="new_password" 
                               placeholder="Masukkan password baru" required minlength="6">
                        <div class="form-text">Password minimal 6 karakter</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm-password" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="confirm-password" 
                               placeholder="Konfirmasi password baru" required minlength="6">
                        <div id="password-match-message" class="form-text"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="reset_password" id="btn-reset-password" class="btn btn-warning">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus User -->
<div class="modal fade" id="hapusUserModal" tabindex="-1" aria-labelledby="hapusUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h1 class="modal-title fs-5" id="hapusUserModalLabel">Konfirmasi Hapus User</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="index.php" method="POST">
        <input type="hidden" id="hapus-user-id" name="user_id">
        <div class="modal-body">
            <div class="text-center">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Yakin ingin menghapus user ini?</h5>
                <p class="mb-2">Nama: <strong id="hapus-user-nama"></strong></p>
                <p class="mb-0">Level: <span id="hapus-user-level" class="badge"></span></p>
                <div class="alert alert-warning mt-3" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Perhatian:</strong> User akan dihapus secara permanen termasuk semua riwayat terkait (jika ada). 
                    Data yang sudah dihapus tidak dapat dikembalikan!
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="hapus_user" class="btn btn-danger">Ya, Hapus User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Fungsi untuk hapus user
function hapusUser(id, nama, level) {
    document.getElementById('hapus-user-id').value = id;
    document.getElementById('hapus-user-nama').textContent = nama;
    
    const levelBadge = document.getElementById('hapus-user-level');
    levelBadge.textContent = level.charAt(0).toUpperCase() + level.slice(1);
    
    // Set badge color based on level
    levelBadge.className = 'badge ';
    switch(level) {
        case 'admin':
            levelBadge.classList.add('bg-danger');
            break;
        case 'operator':
            levelBadge.classList.add('bg-warning');
            break;
        case 'jemaah':
            levelBadge.classList.add('bg-info');
            break;
        default:
            levelBadge.classList.add('bg-secondary');
    }
    
    var modal = new bootstrap.Modal(document.getElementById('hapusUserModal'));
    modal.show();
}

// Fungsi untuk edit user
function editUser(id, username, namaLengkap, userLevel, idJemaah) {
    document.getElementById('edit-user-id').value = id;
    document.getElementById('edit-username').value = username;
    document.getElementById('edit-nama-lengkap').value = namaLengkap;
    document.getElementById('edit-user-level').value = userLevel;
    
    // Handle jemaah selection
    toggleEditJemaahSelect(userLevel);
    if (idJemaah) {
        document.getElementById('edit-id-jemaah').value = idJemaah;
    }
    
    var modal = new bootstrap.Modal(document.getElementById('modalEditUser'));
    modal.show();
}

// Fungsi untuk reset password
function resetPassword(id, namaLengkap) {
    document.getElementById('reset-user-id').value = id;
    document.getElementById('reset-user-nama').textContent = namaLengkap;
    
    // Reset form
    document.getElementById('new-password').value = '';
    document.getElementById('confirm-password').value = '';
    document.getElementById('password-match-message').textContent = '';
    document.getElementById('btn-reset-password').disabled = false;
    
    var modal = new bootstrap.Modal(document.getElementById('modalResetPassword'));
    modal.show();
}

function toggleJemaahSelect(level) {
    const jemaahSelect = document.getElementById('jemaah-select');
    const idJemaahField = document.getElementById('id_jemaah');
    
    if (level === 'jemaah') {
        jemaahSelect.style.display = 'block';
        idJemaahField.required = true;
    } else {
        jemaahSelect.style.display = 'none';
        idJemaahField.required = false;
        idJemaahField.value = '';
    }
}

function toggleEditJemaahSelect(level) {
    const jemaahSelect = document.getElementById('edit-jemaah-select');
    const idJemaahField = document.getElementById('edit-id-jemaah');
    
    if (level === 'jemaah') {
        jemaahSelect.style.display = 'block';
        idJemaahField.required = true;
    } else {
        jemaahSelect.style.display = 'none';
        idJemaahField.required = false;
        idJemaahField.value = '';
    }
}

// Password validation for reset password
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('new-password');
    const confirmPassword = document.getElementById('confirm-password');
    const messageDiv = document.getElementById('password-match-message');
    const submitBtn = document.getElementById('btn-reset-password');
    
    function validatePasswords() {
        if (confirmPassword.value === '') {
            messageDiv.textContent = '';
            messageDiv.className = 'form-text';
            submitBtn.disabled = false;
            return;
        }
        
        if (newPassword.value === confirmPassword.value) {
            messageDiv.textContent = '✓ Password cocok';
            messageDiv.className = 'form-text text-success';
            submitBtn.disabled = false;
        } else {
            messageDiv.textContent = '✗ Password tidak cocok';
            messageDiv.className = 'form-text text-danger';
            submitBtn.disabled = true;
        }
    }
    
    if (newPassword && confirmPassword) {
        newPassword.addEventListener('input', validatePasswords);
        confirmPassword.addEventListener('input', validatePasswords);
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
.btn-sm {
    padding: 0.25rem 0.4rem;
    font-size: 0.875rem;
}
.btn-group {
    flex-wrap: wrap;
    gap: 2px;
}
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
        align-items: stretch;
    }
    .btn-group .btn {
        margin-right: 0;
        margin-bottom: 2px;
    }
}
.table-responsive {
    overflow-x: auto;
}
</style>