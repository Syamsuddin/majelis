# Deskripsi Teknis Aplikasi eMajelis
## Untuk Konteks Agent Coding Qwen

### 📋 Gambaran Umum

eMajelis adalah sistem informasi berbasis web yang dirancang khusus untuk mengelola kegiatan majelis ta'lim dan komunitas keagamaan. Aplikasi ini menyediakan fitur manajemen kehadiran, pelacakan sumbangan, dan pelaporan yang komprehensif dengan antarmuka modern berbasis tema navy blue.

### 🏗️ Arsitektur Sistem

#### Teknologi yang Digunakan
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap 5.3.3, JavaScript, CSS3
- **Icons**: Bootstrap Icons
- **Fonts**: Inter (Google Fonts)

#### Struktur Direktori
```
/Applications/MAMP/htdocs/emajelis/
├── halaman/                 # Direktori halaman utama aplikasi
│   ├── absensi.php          # Halaman absensi dengan scanner barcode
│   ├── dashboard.php        # Dashboard utama dengan statistik
│   ├── jemaah.php           # Manajemen data jemaah
│   ├── kehadiran.php        # Kelola kehadiran lengkap
│   ├── profile.php          # Profil jemaah
│   ├── rekap.php            # Rekap data kehadiran/sumbangan
│   └── sumbangan.php        # Kelola sumbangan
│   └── users.php            # Manajemen user (admin)
├── img/                     # Direktori gambar/logo
├── auth_functions.php       # Fungsi autentikasi dan otorisasi
├── config.php               # Konfigurasi database dan session
├── index.php                # File utama aplikasi dengan routing
├── login.php                # Halaman login sistem
├── setup_database.php       # Setup database dan user default
└── README.md                # Dokumentasi utama
```

### 🔐 Sistem Autentikasi dan Otorisasi

#### Level Pengguna
1. **Admin** (Level tertinggi):
   - Akses penuh ke semua fitur
   - Manajemen user, jemaah, kehadiran, dan sumbangan
   - Konfigurasi sistem

2. **Operator** (Level menengah):
   - Kelola data jemaah
   - Input absensi kehadiran
   - Akses laporan terbatas

3. **Jemaah** (Level dasar):
   - Lihat dashboard pribadi
   - Lihat profil dan riwayat kehadiran
   - Lihat statistik sumbangan pribadi

#### Fungsi Autentikasi Utama
- `isLoggedIn()`: Mengecek apakah user sudah login
- `hasPermission()`: Mengecek level akses user
- `requirePermission()`: Redirect jika tidak memiliki akses
- `loginUser()`: Proses login dengan validasi password
- `logoutUser()`: Proses logout dan destroy session
- `getMenuForUser()`: Mendapatkan menu berdasarkan level user
- `canAccessPage()`: Mengecek akses halaman berdasarkan level

### 🗄️ Struktur Database

#### Tabel Utama
1. **jemaah**:
   - `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
   - `nomor_registrasi` (VARCHAR, UNIQUE)
   - `nama` (VARCHAR)
   - `jenis_kelamin` (ENUM: 'Laki-laki', 'Perempuan')
   - `bin_binti` (VARCHAR)
   - `alamat` (TEXT)
   - `created_at` (TIMESTAMP)

2. **users**:
   - `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
   - `username` (VARCHAR, UNIQUE)
   - `password` (VARCHAR, hashed)
   - `nama_lengkap` (VARCHAR)
   - `user_level` (ENUM: 'admin', 'operator', 'jemaah')
   - `id_jemaah` (INT, FOREIGN KEY ke jemaah.id)
   - `status` (ENUM: 'active', 'inactive')
   - `created_at`, `updated_at` (TIMESTAMP)

3. **kehadiran**:
   - `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
   - `id_jemaah` (INT, FOREIGN KEY ke jemaah.id)
   - `tanggal_hadir` (DATE)
   - `waktu_hadir` (TIME)
   - `created_at` (TIMESTAMP)

4. **sumbangan**:
   - `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
   - `id_kehadiran` (INT, FOREIGN KEY ke kehadiran.id)
   - `jumlah` (DECIMAL)
   - `tanggal_sumbangan` (DATE)
   - `created_at` (TIMESTAMP)

### 🎯 Fitur Utama Aplikasi

#### 1. Dashboard Analytics
- Statistik real-time berbeda untuk setiap level user
- Tampilan khusus admin, operator, dan jemaah

#### 2. Manajemen Jemaah
- CRUD data jemaah
- Cetak kartu anggota dengan barcode
- Validasi data unik

#### 3. Sistem Absensi
- Scan barcode untuk check-in cepat
- Input manual jika tidak ada scanner
- Validasi satu absensi per hari

#### 4. Kelola Kehadiran (Admin)
- Input kehadiran manual
- Edit dan hapus data kehadiran
- Filter berdasarkan tanggal

#### 5. Kelola Sumbangan (Admin)
- Link sumbangan ke kehadiran
- Filter periode (mingguan, bulanan, tahunan)
- Statistik sumbangan

#### 6. Rekap Data
- Laporan kehadiran dan sumbangan
- Filter berdasarkan periode
- Export data (rencana pengembangan)

#### 7. Manajemen User (Admin)
- CRUD user dengan level akses
- Reset password
- Aktivasi/deaktivasi user

### 🎨 Komponen UI/UX

#### Tema Desain
- **Warna Utama**: Gradien navy blue (#0f172a → #64748b)
- **Efek Visual**: Glassmorphism, animasi halus
- **Responsif**: Kompatibel dengan mobile dan desktop

#### Komponen Utama
- Sidebar navigasi dinamis berdasarkan level user
- Card statistik dengan ikon
- Tabel data dengan filter dan search
- Form input dengan validasi
- Modal untuk operasi CRUD

### 🔧 Konfigurasi dan Keamanan

#### Konfigurasi Database
- File `config.php` berisi kredensial database
- Fungsi `createDatabaseConnection()` untuk koneksi
- Prepared statements untuk mencegah SQL injection

#### Keamanan
- Password hashing dengan `password_hash()`
- Session security dengan konfigurasi khusus
- Validasi dan sanitasi input
- Permission-based access control
- Proteksi terhadap CSRF (melalui struktur form)

### 🔄 Alur Kerja Aplikasi

1. **Login** → Autentikasi user
2. **Dashboard** → Tampilan statistik sesuai level
3. **Navigasi** → Menu dinamis berdasarkan level
4. **Operasi** → CRUD sesuai hak akses
5. **Logout** → Destroy session dan redirect

### 📊 Fungsi Penting

#### Fungsi Database
- `createDatabaseConnection()`: Membuat koneksi ke database
- Query dengan prepared statements untuk keamanan

#### Fungsi Helper
- `buatNomorRegistrasi()`: Generate nomor registrasi otomatis
- `getDashboardStats()`: Mendapatkan statistik sesuai level user
- `getMenuForUser()`: Mendapatkan menu berdasarkan level user

#### Fungsi Manajemen Data
- `createUser()`: Membuat user baru dengan validasi
- `loginUser()`: Proses login dengan verifikasi password
- Fungsi CRUD untuk jemaah, kehadiran, sumbangan, dan users

### 📈 Statistik dan Analytics

- Real-time dashboard statistics
- Perhitungan kehadiran harian/bulanan
- Tracking sumbangan dengan filter periode
- Rata-rata sumbangan per transaksi

### 📱 Responsiveness

- Desain mobile-first dengan Bootstrap
- Kompatibel dengan berbagai ukuran layar
- Touch-friendly interface
- Optimized loading performance

### 🛠️ Setup dan Deployment

- Setup database otomatis dengan `setup_database.php`
- User default: admin/admin123, operator/operator123
- Konfigurasi database di `config.php`
- File `.gitignore` untuk keamanan

### 📚 Dokumentasi

- README.md: Dokumentasi utama
- SETUP.md: Panduan instalasi dan deployment
- BUKU-PANDUAN.md: Manual penggunaan sistem
- CONTRIBUTING.md: Panduan kontribusi pengembang