<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Auth;

// Service untuk mengelola business logic terkait Student
class StudentService
{
    // Mengambil daftar siswa dengan filter (role-based access control)
    public function getStudents($filters)
    {
        $query = Student::query();
        $user = Auth::user();

        // Wali Kelas hanya bisa lihat siswa di kelasnya
        if ($user->role === 'wali_kelas' && $user->class) {
            $query->byClass($user->class);
        }

        // Filter pencarian
        if (isset($filters['search']) && $filters['search']) {
            $query->search($filters['search']);
        }

        // Filter kelas (untuk Admin/Guru, wali kelas sudah dibatasi)
        if (isset($filters['class']) && $filters['class']) {
            if ($user->role !== 'wali_kelas') {
                $query->byClass($filters['class']);
            }
        }

        // Filter jenis kelamin
        if (isset($filters['gender']) && $filters['gender']) {
            $query->byGender($filters['gender']);
        }

        $query->orderBy('rombel_absen')->orderBy('name');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    // Membuat student baru dengan tracking
    public function createStudent($data)
    {
        $user = Auth::user();
        $data['last_edited_by'] = $user->id;
        $data['last_edited_ip'] = request()->ip();
        $data['last_edited_at'] = now();

        return Student::create($data);
    }

    // Update student dengan role-based access control
    public function updateStudent($nis, $data)
    {
        $student = Student::findOrFail($nis);
        $user = Auth::user();

        // Wali Kelas hanya bisa update siswa di kelasnya
        if ($user->role === 'wali_kelas') {
            if ($student->class !== $user->class) {
                throw new \Exception("Unauthorized to edit this student");
            }
        }

        $data['last_edited_by'] = $user->id;
        $data['last_edited_ip'] = request()->ip();
        $data['last_edited_at'] = now();

        $student->update($data);
        return $student;
    }

    // Menghapus student dari database
    public function deleteStudent($nis)
    {
        $student = Student::findOrFail($nis);
        $student->delete();
        return true;
    }
}
