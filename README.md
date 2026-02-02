# School Digital Master Book API

Sistem informasi buku induk sekolah digital yang terpusat, modular, dan modern. API ini dibangun menggunakan Laravel 12 dan PHP 8.4 sebagai backend utama untuk mengelola data siswa, alumni, nilai, dan pengguna.

## 🚀 Teknologi

- **Framework:** Laravel 12
- **Language:** PHP 8.4
- **Database:** PostgreSQL 17.7
- **Auth:** Laravel Sanctum
- **Tools:** Composer, Artisan

## 🛠 Instalasi & Setup

Pastikan Anda telah menginstal PHP 8.4+, Composer, dan PostgreSQL.

1. **Clone Repository**

    ```bash
    git clone <repository-url>
    cd school-digital-master-book-api
    ```

2. **Install Dependencies**

    ```bash
    composer install
    ```

3. **Konfigurasi Environment**
   Salin file `.env.example` ke `.env` dan sesuaikan konfigurasi database.

    ```bash
    copy .env.example .env
    ```

    Atur koneksi DB di `.env`:

    ```env
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=db_sekolah
    DB_USERNAME=postgres
    DB_PASSWORD=secret
    ```

4. **Generate Application Key**

    ```bash
    php artisan key:generate
    ```

5. **Migrasi & Seeding Database**

    > **Penting**: Seeding sangat diperlukan agar role dan user default (Admin, Guru, dll) tersedia.

    ```bash
    php artisan migrate:fresh --seed
    ```

6. **Jalankan Server**
    ```bash
    php artisan serve
    ```
    API akan berjalan di `http://localhost:8000`.

## 📚 Dokumentasi API

### Autentikasi

- `POST /api/login` - Login user (email & password)
- `POST /api/logout` - Logout (Wajib Header: `Authorization: Bearer <token>`)
- `GET /api/current-user` - Cek user yang sedang login

### Role: Public (Tanpa Login)

- `GET /api/public/students` - Pencarian siswa (Filter: NIS/NISN)
- `GET /api/public/alumni` - Pencarian alumni (Filter: NIM)
- `GET /api/public/students/{nis}` - Detail siswa terbatas
- `GET /api/public/alumni/{nim}` - Detail alumni terbatas

### Role: Admin (Akses Penuh)

Semua route di bawah `admin.*`:

- **Subjects:** CRUD Mata Pelajaran (`/api/admin/subjects`)
- **Users:** Manajemen User, Role, & Subject Assignment (`/api/admin/users`)
- **Students:** CRUD Data Siswa Lengkap (`/api/admin/students`)
- **Alumni:** CRUD Data Alumni Lengkap (`/api/admin/alumni`)
- **Grades:** Kelola seluruh data nilai siswa (`/api/admin/grades`)
- **Grade Summaries:** Lihat total & rata-rata nilai (`/api/admin/grade-summaries`)
- **Notifications:** Notifikasi update data dari Alumni (`/api/admin/notifications`)

### Role: Guru

Semua route di bawah `guru.*`:

- `GET /api/guru/my-grades` - Lihat nilai mapel yang diampu sendiri.
- `POST /api/guru/my-grades` - Input nilai siswa untuk mapel sendiri.
- `PATCH /api/guru/my-grades/{grade}` - Update nilai.

### Role: Wali Kelas

Semua route di bawah `wali_kelas.*`:

- `GET /api/wali_kelas/wali/students` - Lihat daftar siswa di kelas binaan.
- `GET /api/wali_kelas/wali/grades` - Input nilai semua mapel untuk kelas binaan.
- `GET /api/wali_kelas/wali/grade-summaries` - Rekapitulasi nilai (Total & Rata-rata) per semester.

### Role: Alumni

Semua route di bawah `alumni.*`:

- `GET /api/alumni/my-profile` - Lihat profil diri sendiri.
- `PATCH /api/alumni/my-profile` - Update data karir/kontak (Memicu notifikasi ke Admin).

## 📄 Struktur Folder Penting

- `app/Http/Controllers`: Berisi logika untuk `StudentController`, `GradeController`, `AuthController`, dll.
- `app/Models`: Model Eloquent (`Student`, `Alumni`, `Grade`, `User`, `Subject`).
- `routes/api.php`: Definisi seluruh endpoint API dan grouping middleware.
- `database/migrations`: Struktur tabel database.
- `database/seeders`: Data dummy untuk testing.

## 📜 Lisensi

Hak cipta milik pengembang. Tidak untuk didistribusikan secara publik tanpa izin.
