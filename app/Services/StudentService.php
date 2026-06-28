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
        $query = Student::with('alumni');
        $user = Auth::user();

        // Wali Kelas hanya bisa lihat siswa di kelasnya
        if ($user->role === 'wali_kelas' && $user->classroom_id) {
            $query->byClass($user->classroom_id);
        }

        // Filter pencarian
        if (isset($filters['search']) && $filters['search']) {
            $query->search($filters['search']);
        }

        // Filter kelas (untuk Admin/Guru, wali kelas sudah dibatasi)
        if (isset($filters['classroom_id']) && $filters['classroom_id']) {
            if ($user->role !== 'wali_kelas') {
                $query->byClass($filters['classroom_id']);
            }
        }

        // Filter jenis kelamin
        if (isset($filters['gender']) && $filters['gender']) {
            $query->byGender($filters['gender']);
        }

        $query->orderBy('name');

        return $query->paginate($filters['limit'] ?? $filters['per_page'] ?? 10);
    }

    // Membuat student baru dengan tracking
    public function createStudent($data)
    {
        $user = Auth::user();
        $data['last_edited_by'] = $user->id;
        $data['last_edited_ip'] = request()->ip();
        $data['last_edited_at'] = now();

        $classroomId = $data['classroom_id'] ?? null;
        $academicYearId = $data['academic_year_id'] ?? \App\Models\AcademicYear::orderBy('name', 'desc')->value('id');

        unset($data['classroom_id']);
        unset($data['academic_year_id']);

        $student = Student::create($data);

        if ($classroomId && $academicYearId) {
            $student->classHistories()->attach($classroomId, ['academic_year_id' => $academicYearId]);
        }

        return $student;
    }

    // Update student dengan role-based access control
    public function updateStudent($nis, $data)
    {
        $student = Student::findOrFail($nis);
        $user = Auth::user();

        // Wali Kelas hanya bisa update siswa di kelasnya
        if ($user->role === 'wali_kelas') {
            $isAuthorized = $student->classHistories()->where('classrooms.id', $user->classroom_id)->exists();
            if (!$isAuthorized) {
                throw new \Exception("Unauthorized to edit this student");
            }
        }

        // Gunakan DB transaction untuk menjaga integritas data saat membuat Alumni dan User
        \Illuminate\Support\Facades\DB::transaction(function () use (&$student, &$data, $user) {
            $classroomId = $data['classroom_id'] ?? null;
            $academicYearId = $data['academic_year_id'] ?? \App\Models\AcademicYear::orderBy('name', 'desc')->value('id');
            unset($data['classroom_id']);
            unset($data['academic_year_id']);

            // Handle Alumni logic
            if (isset($data['status']) && $data['status'] === 'alumni') {
                if (!\App\Models\Alumni::where('nis', $student->nis)->exists()) {

                    // Create Alumni aman (hindari duplikat di NIM)
                    $alumni = \App\Models\Alumni::firstOrCreate(
                        ['nim' => $student->nis],
                        [
                            'name' => $student->name,
                            'graduation_year' => date('Y'),
                            'nis' => $student->nis,
                        ]
                    );

                    // Create User aman (hindari duplicate email constraint error)
                    $email = $student->nis . '@school.sch.id';
                    $existingUser = \App\Models\User::where('email', $email)->orWhere('alumni', $alumni->nim)->first();

                    if (!$existingUser) {
                        \App\Models\User::create([
                            'name' => $alumni->name,
                            'email' => $email,
                            'password' => \Illuminate\Support\Facades\Hash::make($student->nis),
                            'role' => 'alumni',
                            'alumni' => $alumni->nim,
                        ]);
                    } else {
                        // Pastikan user eksisting terhubung ke alumni ini
                        $existingUser->update([
                            'role' => 'alumni',
                            'alumni' => $alumni->nim
                        ]);
                    }
                }
                unset($data['status']);
            } elseif (isset($data['status']) && $data['status'] === 'siswa') {
                // Revert status dari alumni ke siswa
                $alumni = \App\Models\Alumni::where('nis', $student->nis)->first();
                if ($alumni) {
                    \App\Models\User::where('alumni', $alumni->nim)->delete();
                    $alumni->delete();
                }
                unset($data['status']);
            } elseif (isset($data['status'])) {
                unset($data['status']);
            }

            $data['last_edited_by'] = $user->id;
            $data['last_edited_ip'] = request()->ip();
            $data['last_edited_at'] = now();

            $student->update($data);

            if ($classroomId && $academicYearId) {
                // Remove existing mapping for this academic year
                \Illuminate\Support\Facades\DB::table('student_classrooms')
                    ->where('student_id', $student->nis)
                    ->where('academic_year_id', $academicYearId)
                    ->delete();

                // Add new mapping
                $student->classHistories()->attach($classroomId, ['academic_year_id' => $academicYearId]);
            }
        });

        return $student->refresh();
    }

    // Menghapus student dari database
    public function deleteStudent($nis)
    {
        $student = Student::findOrFail($nis);
        $student->delete();
        return true;
    }
}
