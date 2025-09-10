# BUKU PANDUAN OPERASIONAL
## Sistem Informasi eMajelis v2.0

![eMajelis](https://img.shields.io/badge/eMajelis-v2.0-blue.svg)

---

### 📖 Daftar Isi
- [1. Pengantar](#1-pengantar)
- [2. Akses Sistem](#2-akses-sistem)
- [3. Dashboard Utama](#3-dashboard-utama)
- [4. Manajemen User](#4-manajemen-user)
- [5. Manajemen Jemaah](#5-manajemen-jemaah)
- [6. Sistem Absensi](#6-sistem-absensi)
- [7. Kelola Kehadiran](#7-kelola-kehadiran)
- [8. Kelola Sumbangan](#8-kelola-sumbangan)
- [9. Rekap Data](#9-rekap-data)
- [10. Profil Jemaah](#10-profil-jemaah)
- [11. Tips dan Troubleshooting](#11-tips-dan-troubleshooting)
- [12. FAQ](#12-faq)

---

## 1. Pengantar

### 1.1 Tentang eMajelis
eMajelis adalah sistem informasi manajemen majelis digital yang dirancang khusus untuk mengelola kegiatan majelis ta'lim dan komunitas keagamaan. Sistem ini menyediakan fitur-fitur modern untuk tracking kehadiran, manajemen sumbangan, dan pelaporan yang komprehensif.

### 1.2 Fitur Utama
- ✅ **Multi-Level Authentication**: Admin, Operator, dan Jemaah
- 📊 **Manajemen Kehadiran**: Scan barcode dan input manual
- 💰 **Manajemen Sumbangan**: Tracking dengan filter periode
- 📈 **Dashboard Analytics**: Statistik real-time
- 🎨 **UI Modern**: Tema navy blue dengan desain responsif
- 📱 **Mobile-Friendly**: Optimized untuk smartphone dan tablet

### 1.3 Level Pengguna

#### 🔴 **ADMIN** (Administrator)
- Akses penuh ke semua fitur sistem
- Kelola user, jemaah, kehadiran, dan sumbangan
- Akses laporan dan analytics lengkap
- Konfigurasi sistem

#### 🟡 **OPERATOR** 
- Kelola data jemaah
- Input absensi kehadiran
- Akses laporan terbatas
- Tidak dapat kelola user

#### 🟢 **JEMAAH**
- Lihat dashboard pribadi
- Lihat profil dan riwayat kehadiran
- Lihat statistik sumbangan pribadi

---

## 2. Akses Sistem

### 2.1 Halaman Login

![Login Interface](docs/screenshots/login.png)

#### Cara Login:
1. **Buka aplikasi** di browser: `http://localhost/emajelis` atau URL yang diberikan admin
2. **Masukkan Username** pada kolom yang tersedia
3. **Masukkan Password** 
4. **Klik tombol "Masuk"** atau tekan Enter

#### Akun Default:
```
🔴 ADMIN
Username: admin
Password: admin123

🟡 OPERATOR  
Username: operator
Password: operator123
```

> ⚠️ **PENTING**: Ubah password default segera setelah login pertama!

### 2.2 Lupa Password
- Hubungi administrator untuk reset password
- Admin dapat mereset password melalui menu "Kelola User"

### 2.3 Logout
- Klik tombol **"Logout"** di bagian bawah sidebar
- Konfirmasi logout saat diminta
- Anda akan diarahkan kembali ke halaman login

---

## 3. Dashboard Utama

### 3.1 Tampilan Dashboard

![Dashboard](docs/screenshots/dashboard.png)

Dashboard menampilkan informasi berbeda sesuai level user:

#### Dashboard Admin
- **Total Jemaah**: Jumlah keseluruhan anggota
- **Kehadiran Hari Ini**: Jumlah yang hadir hari ini
- **Kehadiran Bulan Ini**: Total kehadiran bulan berjalan
- **Sumbangan Bulan Ini**: Total sumbangan bulan berjalan

#### Dashboard Operator
- **Total Jemaah**: Jumlah anggota yang terdaftar
- **Kehadiran Bulan Ini**: Statistik kehadiran

#### Dashboard Jemaah
- **Total Kehadiran Saya**: Riwayat kehadiran pribadi
- **Sumbangan Saya**: Total sumbangan yang diberikan

### 3.2 Sidebar Navigation

#### Menu Admin:
- 🏠 **Dashboard**: Halaman utama dengan statistik
- 👥 **Jemaah**: Manajemen data anggota
- 📱 **Absensi**: Input kehadiran cepat
- 📅 **Kelola Kehadiran**: Manajemen kehadiran lengkap
- 💰 **Kelola Sumbangan**: Manajemen sumbangan
- 📊 **Rekap Data**: Laporan dan analytics
- ⚙️ **Kelola User**: Manajemen akun pengguna

#### Menu Operator:
- 🏠 **Dashboard**: Halaman utama
- 👥 **Jemaah**: Manajemen data anggota
- 📱 **Absensi**: Input kehadiran
- 📊 **Rekap Data**: Laporan terbatas

#### Menu Jemaah:
- 🏠 **Dashboard**: Halaman utama
- 👤 **Profil Saya**: Data pribadi dan riwayat

---

## 4. Manajemen User

> 🔴 **Khusus Admin**

### 4.1 Melihat Daftar User

![User Management](docs/screenshots/users.png)

1. **Klik menu "Kelola User"** di sidebar
2. **Lihat tabel user** dengan informasi:
   - Username
   - Nama Lengkap
   - Level User (Admin/Operator/Jemaah)
   - Status (Aktif/Nonaktif)
   - Jemaah Terkait (jika ada)

### 4.2 Menambah User Baru

1. **Klik tombol "Tambah User"**
2. **Isi form** dengan data:
   - **Username**: Unique, tidak boleh sama
   - **Password**: Minimal 6 karakter
   - **Nama Lengkap**: Nama user
   - **Level User**: Pilih Admin/Operator/Jemaah
   - **Link ke Jemaah**: Jika level Jemaah, pilih data jemaah

3. **Klik "Simpan"**

> 💡 **Tips**: Untuk user level Jemaah, pastikan data jemaah sudah dibuat terlebih dahulu

### 4.3 Edit User

1. **Klik tombol biru (Edit)** pada user yang ingin diubah
2. **Ubah data** yang diperlukan
3. **Klik "Update"**

> ⚠️ **Perhatian**: Username harus tetap unique

### 4.4 Reset Password

1. **Klik tombol kuning (Reset Password)**
2. **Masukkan password baru** (minimal 6 karakter)
3. **Konfirmasi password**
4. **Klik "Reset Password"**

### 4.5 Ubah Status User

1. **Klik tombol status** (Aktif/Nonaktif)
2. **Konfirmasi perubahan**
3. User nonaktif tidak dapat login

### 4.6 Hapus User

1. **Klik tombol merah (Hapus)**
2. **Baca peringatan** dengan seksama
3. **Ketik "HAPUS"** untuk konfirmasi
4. **Klik "Ya, Hapus User"**

> ⚠️ **PENTING**: 
> - Tidak dapat menghapus user yang sedang login
> - Tidak dapat menghapus admin terakhir
> - Data yang dihapus tidak dapat dikembalikan

---

## 5. Manajemen Jemaah

> 🔴 **Admin** dan 🟡 **Operator**

### 5.1 Melihat Daftar Jemaah

![Jemaah Management](docs/screenshots/jemaah.png)

1. **Klik menu "Jemaah"** di sidebar
2. **Lihat tabel jemaah** dengan informasi:
   - Nomor Registrasi (Unique ID)
   - Nama Lengkap
   - Jenis Kelamin
   - Bin/Binti (Nama Ayah)
   - Alamat

### 5.2 Menambah Jemaah Baru

1. **Klik tombol "Tambah Jemaah"**
2. **Isi form lengkap**:
   - **Nomor Registrasi**: Automatic atau manual (harus unique)
   - **Nama Lengkap**: Nama jemaah
   - **Jenis Kelamin**: Laki-laki/Perempuan
   - **Bin/Binti**: Nama ayah
   - **Alamat**: Alamat lengkap

3. **Klik "Simpan"**

> 💡 **Tips**: Nomor registrasi akan digunakan untuk barcode absensi

### 5.3 Edit Data Jemaah

1. **Klik tombol biru (Edit)** pada jemaah yang ingin diubah
2. **Ubah data** yang diperlukan
3. **Klik "Update"**

### 5.4 Hapus Jemaah

1. **Klik tombol merah (Hapus)**
2. **Konfirmasi penghapusan**

> ⚠️ **Perhatian**: Jemaah yang memiliki riwayat kehadiran tidak dapat dihapus

### 5.5 Cetak Kartu Anggota

1. **Klik tombol "Tampilkan Kartu"** pada jemaah
2. **Kartu akan muncul** dengan barcode
3. **Klik "Print"** untuk mencetak

![Member Card](docs/screenshots/card.png)

> 💡 **Tips**: Kartu berisi barcode yang dapat di-scan untuk absensi

---

## 6. Sistem Absensi

> 🔴 **Admin** dan 🟡 **Operator**

### 6.1 Halaman Absensi

![Absensi Interface](docs/screenshots/absensi.png)

1. **Klik menu "Absensi"** di sidebar
2. **Halaman absensi** akan terbuka dengan:
   - Field scan barcode
   - Field input sumbangan
   - Tombol "Catat Kehadiran"

### 6.2 Absensi via Barcode

#### Cara Scan Barcode:
1. **Siapkan kartu anggota** dengan barcode
2. **Klik field "Scan Barcode"**
3. **Scan barcode** menggunakan:
   - Webcam laptop/komputer
   - Scanner barcode
   - Ketik manual nomor registrasi

4. **Atur jumlah sumbangan** (default Rp 1.000)
5. **Klik "Catat Kehadiran"** atau tekan Enter

### 6.3 Input Manual

Jika tidak ada scanner:
1. **Ketik nomor registrasi** secara manual
2. **Atur jumlah sumbangan**
3. **Klik "Catat Kehadiran"**

### 6.4 Notifikasi Sistem

Sistem akan memberikan notifikasi:
- ✅ **Berhasil**: "Kehadiran [Nama] berhasil dicatat"
- ⚠️ **Warning**: "Sudah tercatat hadir hari ini"
- ❌ **Error**: "Nomor registrasi tidak ditemukan"

> 💡 **Tips**: Satu jemaah hanya bisa absen sekali per hari

---

## 7. Kelola Kehadiran

> 🔴 **Khusus Admin**

### 7.1 Melihat Data Kehadiran

![Attendance Management](docs/screenshots/attendance.png)

1. **Klik menu "Kelola Kehadiran"**
2. **Lihat tabel kehadiran** dengan:
   - Tanggal Hadir
   - Waktu Hadir
   - Nama Jemaah
   - Nomor Registrasi
   - Sumbangan

### 7.2 Filter Data

#### Filter Tanggal:
1. **Pilih tanggal** pada field "Tanggal"
2. **Klik "Filter"**

#### Search:
1. **Ketik nama atau nomor registrasi** pada field search
2. **Klik "Filter"**

#### Reset Filter:
- **Klik "Reset"** untuk menampilkan semua data

### 7.3 Tambah Kehadiran Manual

1. **Klik "Tambah Kehadiran"**
2. **Isi form**:
   - **Jemaah**: Pilih dari dropdown
   - **Tanggal Hadir**: Pilih tanggal
   - **Waktu Hadir**: Set waktu (default: sekarang)
   - **Sumbangan**: Masukkan nominal

3. **Klik "Simpan Kehadiran"**

### 7.4 Edit Kehadiran

1. **Klik tombol kuning (Edit)**
2. **Ubah data** yang diperlukan
3. **Klik "Update Kehadiran"**

### 7.5 Hapus Kehadiran

1. **Klik tombol merah (Hapus)**
2. **Baca konfirmasi** dengan seksama
3. **Klik "Ya, Hapus"**

> ⚠️ **Perhatian**: Data kehadiran yang dihapus akan menghapus sumbangan terkait

### 7.6 Statistik Kehadiran

Panel statistik menampilkan:
- **Total Kehadiran** (periode filter)
- **Total Sumbangan** (periode filter)

---

## 8. Kelola Sumbangan

> 🔴 **Khusus Admin**

### 8.1 Tampilan Kelola Sumbangan

![Donation Management](docs/screenshots/donations.png)

1. **Klik menu "Kelola Sumbangan"**
2. **Lihat interface** dengan:
   - Filter periode dan tanggal
   - Tabel data sumbangan
   - Statistik sumbangan

### 8.2 Filter Periode

#### Filter Periode Cepat:
- **Minggu Ini**: Sumbangan minggu berjalan
- **Bulan Ini**: Sumbangan bulan berjalan  
- **Tahun Ini**: Sumbangan tahun berjalan
- **Minggu Lalu**: Sumbangan minggu sebelumnya
- **Bulan Lalu**: Sumbangan bulan sebelumnya
- **Tahun Lalu**: Sumbangan tahun sebelumnya

#### Filter Tanggal Spesifik:
1. **Pilih tanggal** pada field "Tanggal Spesifik"
2. **Sistem otomatis** akan filter data

#### Search:
- **Ketik nama atau nomor registrasi** untuk pencarian

### 8.3 Statistik Sumbangan

Panel statistik menampilkan:
- **Total Sumbangan**: Jumlah donasi sesuai filter
- **Jumlah Record**: Banyaknya transaksi
- **Rata-rata Sumbangan**: Average per transaksi

### 8.4 Tambah Sumbangan

1. **Klik "Tambah Sumbangan"**
2. **Isi form**:
   - **Pilih Kehadiran**: Link ke data kehadiran yang ada
   - **Tanggal Sumbangan**: Set tanggal
   - **Jumlah Sumbangan**: Masukkan nominal (Rupiah)

3. **Klik "Simpan Sumbangan"**

> 💡 **Tips**: Sumbangan harus terkait dengan data kehadiran

### 8.5 Edit Sumbangan

1. **Klik tombol kuning (Edit)**
2. **Ubah data** sesuai kebutuhan
3. **Klik "Update Sumbangan"**

### 8.6 Hapus Sumbangan

1. **Klik tombol merah (Hapus)**
2. **Konfirmasi penghapusan**
3. **Klik "Ya, Hapus"**

### 8.7 Tips Manajemen Sumbangan

- **Gunakan filter periode** untuk analisis trend
- **Pantau rata-rata sumbangan** untuk insight
- **Export data** untuk laporan eksternal
- **Backup data** secara berkala

---

## 9. Rekap Data

> 🔴 **Admin** dan 🟡 **Operator**

### 9.1 Halaman Rekap

![Reports Interface](docs/screenshots/reports.png)

1. **Klik menu "Rekap Data"**
2. **Pilih jenis rekap**:
   - **Rekap Kehadiran**: Data kehadiran jemaah
   - **Rekap Sumbangan**: Data donasi

### 9.2 Filter Rekap

#### Filter Jenis:
- **Kehadiran**: Tampilkan data kehadiran
- **Sumbangan**: Tampilkan data sumbangan

#### Filter Periode:
1. **Dari Tanggal**: Tanggal mulai
2. **Sampai Tanggal**: Tanggal akhir
3. **Klik "Filter"**

#### Search:
- **Nama Jemaah**: Cari berdasarkan nama

### 9.3 Rekap Kehadiran

Menampilkan data:
- Nama Jemaah
- Nomor Registrasi
- Tanggal Hadir
- Waktu Hadir

### 9.4 Rekap Sumbangan

Menampilkan data:
- Nama Jemaah
- Nomor Registrasi
- Tanggal Sumbangan
- Jumlah Sumbangan
- **Total Sumbangan** di bagian bawah

### 9.5 Export Data

> 💡 **Catatan**: Fitur export sedang dalam pengembangan

Untuk sementara dapat:
1. **Copy data** dari tabel
2. **Paste ke Excel/Spreadsheet**
3. **Screenshot** untuk dokumentasi

---

## 10. Profil Jemaah

> 🟢 **Khusus Jemaah**

### 10.1 Halaman Profil

![Profile Interface](docs/screenshots/profile.png)

1. **Login sebagai jemaah**
2. **Klik menu "Profil Saya"**

### 10.2 Informasi Profil

#### Data Pribadi:
- Nama Lengkap
- Nomor Registrasi
- Jenis Kelamin
- Bin/Binti
- Alamat

#### Statistik Pribadi:
- **Total Kehadiran**: Jumlah kehadiran keseluruhan
- **Kehadiran Bulan Ini**: Kehadiran bulan berjalan
- **Sumbangan Bulan Ini**: Total sumbangan bulan ini

### 10.3 Riwayat Kehadiran

Tabel menampilkan:
- Tanggal Hadir
- Waktu Hadir
- Sumbangan (jika ada)

> 💡 **Tips**: Data riwayat menampilkan 10 kehadiran terakhir

### 10.4 Tips untuk Jemaah

- **Bawa kartu anggota** saat majelis
- **Pastikan barcode** tidak rusak
- **Cek profil berkala** untuk monitoring kehadiran
- **Hubungi admin** jika ada kesalahan data

---

## 11. Tips dan Troubleshooting

### 11.1 Tips Umum

#### Untuk Admin:
- **Backup data** secara berkala
- **Monitor statistik** harian
- **Update password** secara berkala
- **Pantau aktivitas user**

#### Untuk Operator:
- **Pastikan scanner** berfungsi baik
- **Cek koneksi internet** saat input data
- **Verifikasi data** sebelum save

#### Untuk Jemaah:
- **Simpan kartu anggota** dengan baik
- **Cek profil** secara berkala
- **Laporkan masalah** ke admin

### 11.2 Troubleshooting

#### Masalah Login:
**Gejala**: Tidak bisa login
**Solusi**:
1. Cek username dan password
2. Pastikan akun aktif
3. Hubungi admin untuk reset password
4. Clear browser cache

#### Masalah Scanner:
**Gejala**: Barcode tidak terbaca
**Solusi**:
1. Bersihkan kamera/scanner
2. Pastikan pencahayaan cukup
3. Gunakan input manual
4. Ganti kartu jika rusak

#### Data Tidak Muncul:
**Gejala**: Data kosong atau tidak update
**Solusi**:
1. Refresh halaman (F5)
2. Cek filter yang diterapkan
3. Pastikan koneksi internet stabil
4. Logout dan login kembali

#### Error Saat Save:
**Gejala**: Data tidak tersimpan
**Solusi**:
1. Cek validasi form
2. Pastikan data required diisi
3. Cek koneksi database
4. Hubungi admin

### 11.3 Perawatan Sistem

#### Harian:
- Backup database
- Monitor log error
- Cek statistik kehadiran

#### Mingguan:
- Update password admin
- Review data jemaah
- Backup file sistem

#### Bulanan:
- Audit data user
- Cleanup data temporary
- Update sistem (jika ada)

---

## 12. FAQ (Frequently Asked Questions)

### 12.1 Pertanyaan Umum

**Q: Bagaimana cara mengganti password?**
A: Login → Hubungi admin untuk reset password melalui menu "Kelola User"

**Q: Apakah bisa akses dari smartphone?**
A: Ya, sistem responsive dan dapat diakses dari smartphone/tablet

**Q: Bagaimana jika lupa nomor registrasi?**
A: Hubungi admin atau operator untuk mendapatkan informasi nomor registrasi

**Q: Bisakah satu jemaah absen beberapa kali dalam sehari?**
A: Tidak, sistem membatasi 1 absensi per jemaah per hari

### 12.2 Pertanyaan Admin

**Q: Bagaimana cara backup data?**
A: Export database MySQL atau gunakan tools backup otomatis

**Q: Bisakah menghapus data kehadiran?**
A: Ya, admin dapat menghapus data kehadiran melalui menu "Kelola Kehadiran"

**Q: Bagaimana cara melihat laporan bulanan?**
A: Gunakan menu "Rekap Data" dengan filter periode yang diinginkan

**Q: Apakah ada batasan jumlah jemaah?**
A: Tidak ada batasan khusus, tergantung kapasitas server dan database

### 12.3 Pertanyaan Operator

**Q: Bisakah operator menghapus jemaah?**
A: Ya, operator dapat menghapus jemaah yang belum memiliki riwayat kehadiran

**Q: Bagaimana jika scanner rusak?**
A: Gunakan input manual dengan mengetik nomor registrasi

**Q: Bisakah mengubah nominal sumbangan default?**
A: Ya, ubah nilai pada field sumbangan sebelum klik "Catat Kehadiran"

### 12.4 Pertanyaan Jemaah

**Q: Bagaimana cara melihat total sumbangan saya?**
A: Login ke profil jemaah → Lihat statistik "Sumbangan Bulan Ini"

**Q: Apakah data saya aman?**
A: Ya, sistem menggunakan enkripsi dan security standard untuk melindungi data

**Q: Bisakah mengubah data pribadi?**
A: Hubungi admin atau operator untuk mengubah data pribadi

---

## 📞 Kontak dan Dukungan

### Tim Support:
- **Admin Sistem**: [admin@emajelis.com](mailto:admin@emajelis.com)
- **Technical Support**: [support@emajelis.com](mailto:support@emajelis.com)
- **Dokumentasi**: [docs.emajelis.com](https://docs.emajelis.com)

### Jam Operasional:
- **Senin - Jumat**: 08:00 - 17:00 WIB
- **Sabtu**: 08:00 - 12:00 WIB
- **Minggu**: Libur (Emergency only)

---

## 📋 Informasi Versi

**Versi Aplikasi**: 2.0  
**Tanggal Update**: 2024  
**Kompatibilitas**: PHP 7.4+, MySQL 5.7+  
**Browser Support**: Chrome, Firefox, Safari, Edge  

---

**© 2024 eMajelis - Sistem Informasi Majelis Digital**  
*Dibuat dengan ❤️ untuk komunitas Muslim Indonesia*

---

> 💡 **Catatan**: Buku panduan ini akan terus diperbarui seiring dengan pengembangan fitur baru. Silakan cek versi terbaru di website resmi atau hubungi admin sistem.