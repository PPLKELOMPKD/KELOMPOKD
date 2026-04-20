# Rancangan Sistem Sprint 1 SIKARA

## Arsitektur Tingkat Tinggi
SIKARA Sprint 1 dibangun sebagai aplikasi web monolitik menggunakan Laravel untuk backend, Vue.js dengan Inertia untuk antarmuka, MySQL untuk penyimpanan data, dan DOMPDF untuk pembuatan CV.

```mermaid
flowchart LR
    A["User Browser"] --> B["Vue.js + Inertia UI"]
    B --> C["Laravel Web App"]
    C --> D["MySQL Database"]
    C --> E["CV PDF Generator"]
```

## Use Case Diagram
```mermaid
flowchart LR
    M["Mahasiswa"] --> UC1["Login"]
    M --> UC2["Kelola Profil"]
    M --> UC3["Kelola Skill"]
    M --> UC4["Lihat Lowongan"]
    M --> UC5["Apply Magang"]
    M --> UC6["Lihat Notifikasi"]
    M --> UC7["Unduh CV"]

    P["Mitra Perusahaan"] --> UC1
    P --> UC8["Lihat Dashboard Stub"]

    A["Admin"] --> UC1
    A --> UC9["Lihat Dashboard Stub"]
```

## Activity Flow
### Login
```mermaid
flowchart TD
    A["Buka Halaman Login"] --> B["Isi Email, Password, Role"]
    B --> C{"Kredensial Valid?"}
    C -- "Ya" --> D["Buat Session"]
    D --> E["Redirect ke Dashboard Sesuai Role"]
    C -- "Tidak" --> F["Tampilkan Error Login"]
```

### Edit Profil Mahasiswa
```mermaid
flowchart TD
    A["Mahasiswa Buka Halaman Profil"] --> B["Ubah Data Diri dan Akademik"]
    B --> C["Submit Form"]
    C --> D{"Validasi Berhasil?"}
    D -- "Ya" --> E["Simpan Mahasiswa Profile"]
    E --> F["Perbarui Indikator Kelengkapan Profil"]
    D -- "Tidak" --> G["Tampilkan Error Validasi"]
```

### Apply Internship
```mermaid
flowchart TD
    A["Mahasiswa Lihat Daftar Lowongan"] --> B["Pilih Lowongan"]
    B --> C["Klik Apply"]
    C --> D{"Sudah Pernah Melamar?"}
    D -- "Tidak" --> E["Buat Record Application"]
    E --> F["Set Status submitted"]
    F --> G["Buat Notifikasi"]
    D -- "Ya" --> H["Tolak Duplikasi Lamaran"]
```

### Generate CV
```mermaid
flowchart TD
    A["Mahasiswa Klik Generate CV"] --> B["Ambil Data Profil dan Skill"]
    B --> C["Render Template CV Blade"]
    C --> D["Generate PDF dengan DOMPDF"]
    D --> E["Download File CV"]
```

## Struktur Data
### Entitas Utama
- `users`: menyimpan email, password terenkripsi, dan role.
- `mahasiswa_profiles`: menyimpan identitas mahasiswa dan data akademik.
- `skills`: menyimpan skill mahasiswa dan persentase penguasaan.
- `internships`: menyimpan lowongan magang.
- `applications`: menyimpan relasi mahasiswa terhadap lowongan dan status lamaran.
- `notifications`: menyimpan notifikasi pengguna.

### ERD Konseptual
```mermaid
erDiagram
    USERS ||--o| MAHASISWA_PROFILES : has
    USERS ||--o{ APPLICATIONS : submits
    USERS ||--o{ NOTIFICATIONS : receives
    MAHASISWA_PROFILES ||--o{ SKILLS : owns
    INTERNSHIPS ||--o{ APPLICATIONS : receives

    USERS {
        bigint id
        string name
        string email
        string password
        string role
    }

    MAHASISWA_PROFILES {
        bigint id
        bigint user_id
        string nim
        string full_name
        string department
        decimal gpa
    }

    SKILLS {
        bigint id
        bigint mahasiswa_profile_id
        string name
        integer proficiency
    }

    INTERNSHIPS {
        bigint id
        string title
        string company_name
        date deadline
        string status
    }

    APPLICATIONS {
        bigint id
        bigint user_id
        bigint internship_id
        string status
    }

    NOTIFICATIONS {
        bigint id
        bigint user_id
        string title
        boolean is_read
    }
```

## Pemetaan Implementasi
- Autentikasi dan session: Laravel Breeze.
- Middleware role: `EnsureUserHasRole`.
- Dashboard role-based: `DashboardController`.
- Profil mahasiswa: `StudentProfileController`.
- Skill: `SkillController`.
- Magang dan pendaftaran: `InternshipController` dan `ApplicationController`.
- Notifikasi: `NotificationController`.
- CV PDF: `CvController` dan `resources/views/pdf/cv.blade.php`.

## Rute Sprint 1
- `POST /login`
- `POST /logout`
- `GET /dashboard`
- `GET /profile`
- `POST /profile`
- `POST /profile/skills`
- `GET /internships`
- `POST /internship-apply`
- `GET /notifications`
- `POST /notifications/{id}/read`
- `GET /cv/download`

## Batas Implementasi
- Event dan statistik tetap dimodelkan sebagai arah pengembangan berikutnya, namun belum diimplementasikan penuh pada Sprint 1.
- Dashboard perusahaan dan admin masih berperan sebagai placeholder fungsional agar validasi multi-role dapat diuji sejak awal.
