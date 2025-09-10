<?php
// Cek akses - hanya admin dan operator yang bisa mengakses halaman ini
requirePermission(['admin', 'operator']);
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Data Jemaah</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahJemaahModal">
            <i class="bi bi-plus-circle-fill me-2"></i>Tambah Jemaah
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>No. Registrasi</th>
                            <th>Nama</th>
                            <th>Jenis Kelamin</th>
                            <th>Bin/Binti</th>
                            <th>Alamat</th>
                            <th width="150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM jemaah ORDER BY id DESC";
                        $result = mysqli_query($koneksi, $query);
                        $no = 1;
                        if(mysqli_num_rows($result) > 0):
                            while($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><span class="badge bg-secondary"><?= $row['nomor_registrasi'] ?></span></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= $row['jenis_kelamin'] ?></td>
                            <td><?= htmlspecialchars($row['bin_binti']) ?></td>
                            <td><?= htmlspecialchars($row['alamat']) ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-info text-white" onclick="tampilkanKartu('<?= $row['nomor_registrasi'] ?>', '<?= htmlspecialchars(addslashes($row['nama'])) ?>')" title="Lihat Kartu">
                                        <i class="bi bi-person-badge"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="editJemaah(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama'])) ?>', '<?= $row['jenis_kelamin'] ?>', '<?= htmlspecialchars(addslashes($row['bin_binti'])) ?>', '<?= htmlspecialchars(addslashes($row['alamat'])) ?>')" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="hapusJemaah(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama'])) ?>')" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data jemaah.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Jemaah -->
<div class="modal fade" id="tambahJemaahModal" tabindex="-1" aria-labelledby="tambahJemaahModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="tambahJemaahModalLabel">Form Tambah Jemaah Baru</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="index.php" method="POST">
        <div class="modal-body">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
            <div class="mb-3">
                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="bin_binti" class="form-label">Bin / Binti</label>
                <input type="text" class="form-control" id="bin_binti" name="bin_binti">
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="tambah_jemaah" class="btn btn-primary">Simpan Data</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Tampilkan Kartu & Barcode -->
<div class="modal fade" id="kartuModal" tabindex="-1" aria-labelledby="kartuModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="kartuModalLabel">Kartu Anggota Jemaah</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-4">
        <h4 id="nama-kartu" class="mb-3"></h4>
        <svg id="barcode" class="img-fluid"></svg>
        <p id="nomor-registrasi-kartu" class="mt-2 fw-bold"></p>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer-fill me-2"></i>Cetak</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Jemaah -->
<div class="modal fade" id="editJemaahModal" tabindex="-1" aria-labelledby="editJemaahModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="editJemaahModalLabel">Edit Data Jemaah</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="index.php" method="POST">
        <input type="hidden" id="edit-id" name="id_jemaah">
        <div class="modal-body">
            <div class="mb-3">
                <label for="edit-nama" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="edit-nama" name="nama" required>
            </div>
            <div class="mb-3">
                <label for="edit-jenis-kelamin" class="form-label">Jenis Kelamin</label>
                <select class="form-select" id="edit-jenis-kelamin" name="jenis_kelamin" required>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="edit-bin-binti" class="form-label">Bin / Binti</label>
                <input type="text" class="form-control" id="edit-bin-binti" name="bin_binti">
            </div>
            <div class="mb-3">
                <label for="edit-alamat" class="form-label">Alamat</label>
                <textarea class="form-control" id="edit-alamat" name="alamat" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="edit_jemaah" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="hapusJemaahModal" tabindex="-1" aria-labelledby="hapusJemaahModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h1 class="modal-title fs-5" id="hapusJemaahModalLabel">Konfirmasi Hapus</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="index.php" method="POST">
        <input type="hidden" id="hapus-id" name="id_jemaah">
        <div class="modal-body">
            <div class="text-center">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Yakin ingin menghapus data jemaah?</h5>
                <p class="mb-0">Nama: <strong id="hapus-nama"></strong></p>
                <p class="text-muted small mt-2">Data yang sudah dihapus tidak dapat dikembalikan!</p>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="hapus_jemaah" class="btn btn-danger">Ya, Hapus Data</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Fungsi untuk edit jemaah
function editJemaah(id, nama, jenisKelamin, binBinti, alamat) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-jenis-kelamin').value = jenisKelamin;
    document.getElementById('edit-bin-binti').value = binBinti;
    document.getElementById('edit-alamat').value = alamat;
    
    var modal = new bootstrap.Modal(document.getElementById('editJemaahModal'));
    modal.show();
}

// Fungsi untuk hapus jemaah
function hapusJemaah(id, nama) {
    document.getElementById('hapus-id').value = id;
    document.getElementById('hapus-nama').textContent = nama;
    
    var modal = new bootstrap.Modal(document.getElementById('hapusJemaahModal'));
    modal.show();
}
</script>

<style>
.btn-group .btn {
    margin-right: 2px;
}
.btn-group .btn:last-child {
    margin-right: 0;
}
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>