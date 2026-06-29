# 🏫 School Digital Master Book - API

[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-17.7-316192?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)

Sistem informasi buku induk sekolah digital yang terpusat, modular, dan modern. API ini dibangun menggunakan **Laravel 12** dan **PHP 8.4** sebagai backend utama untuk mengelola data siswa, alumni, nilai, pengguna (Admin, Guru, Wali Kelas), kelas, tahun ajaran, dan mata pelajaran.

---

## 🚀 Teknologi Utama

- **Framework:** Laravel 12.0
- **Language:** PHP 8.4
- **Database:** PostgreSQL 17.7
- **Authentication:** Laravel Sanctum (Token-based Auth)
- **Tools:** Composer, Artisan, Pest (Testing)

## 🛠 Instalasi & Setup

Pastikan Anda telah menginstal PHP 8.4+, Composer, dan PostgreSQL di sistem Anda.

1. **Clone Repository**
   ```bash
   git clone https://github.com/mfl4/school-digital-master-book-api.git
   cd school-digital-master-book-api
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**
   Salin file `.env.example` ke `.env` dan sesuaikan konfigurasi database.
   ```bash
   copy .env.example .env  # (Windows)
   # atau
   cp .env.example .env    # (Mac/Linux)
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
   > **Penting**: Seeding sangat diperlukan agar role, user default (Admin, Guru, dll), kelas, dan tahun ajaran tersedia.
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Jalankan Server**
   ```bash
   php artisan serve
   ```
   API akan berjalan di `http://127.0.0.1:8000`.

---

## 📚 Struktur API & Fitur

Sistem ini menerapkan Role-Based Access Control (RBAC).

### Autentikasi (`/api`)
- `POST /login` - Login pengguna (mengembalikan token Sanctum).
- `POST /logout` - Logout (Wajib Header `Authorization: Bearer <token>`).
- `GET /current-user` - Mendapatkan data user yang sedang login.

### 🌐 Role: Public (Tanpa Login)
- `GET /public/students` - Pencarian siswa (Filter: NIS/NISN).
- `GET /public/alumni` - Pencarian alumni (Filter: NIM).
- `GET /public/students/{nis}` - Detail siswa terbatas.
- `GET /public/alumni/{nim}` - Detail alumni terbatas.

### 👑 Role: Admin (Akses Penuh)
Admin memiliki kontrol penuh atas master data. Prefix: `/api/admin/`
- **Academic Years:** CRUD Tahun Ajaran (`/api/admin/academic-years`).
- **Classrooms:** CRUD Data Kelas (`/api/admin/classrooms`).
- **Subjects:** CRUD Mata Pelajaran (`/api/admin/subjects`).
- **Users:** Manajemen User, Role, & Penugasan (Subject/Class Assignment) (`/api/admin/users`).
- **Students:** CRUD Data Siswa Lengkap (`/api/admin/students`).
- **Alumni:** CRUD Data Alumni Lengkap (`/api/admin/alumni`).
- **Grades:** Kelola seluruh data nilai siswa (`/api/admin/grades`).
- **Grade Summaries:** Lihat total & rata-rata nilai siswa (`/api/admin/grade-summaries`).
- **Notifications:** Mengelola notifikasi pembaruan data dari Alumni (`/api/admin/notifications`).

### 👨‍🏫 Role: Guru
Guru mengelola nilai untuk mata pelajaran yang ditugaskan kepada mereka. Prefix: `/api/guru/`
- `GET /my-grades` - Lihat nilai mapel yang diampu sendiri.
- `POST /my-grades` - Input nilai siswa untuk mapel sendiri.
- `PATCH /my-grades/{grade}` - Update nilai.

### 👔 Role: Wali Kelas
Wali kelas memantau siswa di kelas binaannya. Prefix: `/api/wali_kelas/wali/`
- `GET /students` - Lihat daftar siswa di kelas binaan.
- `GET /grades` - Input/lihat nilai semua mapel untuk kelas binaan.
- `GET /grade-summaries` - Rekapitulasi nilai (Total & Rata-rata) per semester.

### 🎓 Role: Alumni
Alumni dapat memperbarui data mereka sendiri. Prefix: `/api/alumni/`
- `GET /my-profile` - Lihat profil diri sendiri.
- `PATCH /my-profile` - Update data karir/universitas/kontak (Memicu notifikasi ke Admin).

---

## 📄 Struktur Proyek (Backend)

- `app/Http/Controllers/`: Berisi controller utama seperti `StudentController`, `GradeController`, `AlumniController`, `AcademicYearController`, `ClassroomController`, dll.
- `app/Models/`: Model Eloquent untuk representasi tabel database.
- `routes/api.php`: Definisi seluruh endpoint API dan pengelompokan (grouping) middleware role.
- `database/migrations/`: Skema tabel database PostgreSQL.
- `database/seeders/`: Data dummy (factory & seeder) untuk keperluan *testing* dan instalasi awal.

## 📜 Lisensi
Dikembangkan untuk keperluan internal dan manajemen sekolah. Tidak untuk didistribusikan secara publik tanpa izin.
