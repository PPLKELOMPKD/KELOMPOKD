# Kebutuhan Sistem Sprint 1 SIKARA

## Ruang Lingkup
Sprint 1 SIKARA berfokus pada fondasi sistem informasi karir mahasiswa dengan tiga peran pengguna:

- `Mahasiswa`
- `Mitra Perusahaan`
- `Admin`

Implementasi sprint ini memprioritaskan alur utama mahasiswa, sementara fitur perusahaan dan admin masih berupa akses login dan dashboard stub agar struktur multi-role sudah terbukti berjalan.

## Kebutuhan Fungsional
### Auth
- Sistem harus menyediakan halaman login dengan pilihan peran pengguna.
- Sistem harus memverifikasi kombinasi email, password, dan role.
- Sistem harus mengarahkan pengguna ke dashboard setelah login berhasil.
- Sistem harus menolak akses ke halaman yang tidak sesuai dengan role pengguna.
- Sistem harus menyediakan fitur logout.

### Profil Mahasiswa
- Mahasiswa harus dapat melihat profil akademik dan personal.
- Mahasiswa harus dapat memperbarui data profil berupa NIM, nama lengkap, program studi, IPK, email kontak, nomor telepon, bio singkat, dan alamat.
- Sistem harus menampilkan indikator kelengkapan profil.

### Keterampilan
- Mahasiswa harus dapat menambah keterampilan.
- Mahasiswa harus dapat menyimpan tingkat penguasaan keterampilan dalam bentuk persentase.
- Data keterampilan harus terhubung ke profil mahasiswa yang sedang login.

### Lowongan Magang
- Mahasiswa harus dapat melihat daftar lowongan magang yang aktif.
- Mahasiswa harus dapat melihat ringkasan perusahaan, persyaratan, lokasi, dan deadline lowongan.
- Mahasiswa harus dapat mengajukan lamaran ke lowongan yang tersedia.
- Sistem harus menyimpan status awal pendaftaran sebagai `submitted`.

### Notifikasi
- Sistem harus menyimpan notifikasi untuk pengguna.
- Mahasiswa harus dapat melihat daftar notifikasi.
- Sistem harus menampilkan jumlah notifikasi yang belum dibaca.
- Mahasiswa harus dapat menandai notifikasi sebagai sudah dibaca.

### CV Generator
- Mahasiswa harus dapat mengunduh CV dalam format PDF.
- Data CV harus diambil dari profil mahasiswa dan daftar keterampilan yang tersimpan di sistem.

### Dashboard
- Mahasiswa harus melihat ringkasan jumlah lamaran, jumlah skill, dan notifikasi terbaru.
- Mitra Perusahaan harus dapat login dan melihat dashboard stub.
- Admin harus dapat login dan melihat dashboard stub.

## Kebutuhan Non-Fungsional
- Sistem harus menggunakan password yang di-hash.
- Sistem harus membatasi akses berdasarkan role pengguna.
- Sistem harus responsif pada desktop dan laptop standar.
- Sistem harus memiliki struktur kode yang mudah dikembangkan untuk sprint berikutnya.
- Sistem harus mendukung data demo melalui seeder untuk kebutuhan presentasi.
- Sistem harus dapat menghasilkan PDF CV tanpa layanan eksternal tambahan.

## Batasan Sprint 1
- Fitur posting lowongan oleh perusahaan belum diaktifkan penuh.
- Fitur pengelolaan event masih berupa placeholder pada struktur dan navigasi.
- Statistik lanjutan untuk admin dan perusahaan belum diimplementasikan penuh.
- Notifikasi real-time berbasis WebSocket belum menjadi target sprint ini.
