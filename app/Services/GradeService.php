<?php

namespace App\Services;

use App\Models\Grade;
use Illuminate\Support\Facades\Auth;

// Service untuk mengelola business logic terkait Grade dengan role-based access
class GradeService
{
    // Mengambil daftar nilai dengan filter (role-based access control)
    public function getGrades($filters)
    {
        $query = Grade::with(['student', 'subject']);

        // Filter berdasarkan student
        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        // Filter berdasarkan subject
        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        // Filter berdasarkan semester
        if (isset($filters['semester'])) {
            $query->where('semester', $filters['semester']);
        }

        // Filter berdasarkan kelas
        if (isset($filters['class'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('rombel_absen', 'like', $filters['class'] . '%');
            });
        }

        // Role-based access control
        $user = Auth::user();
        if ($user) {
            // Guru hanya bisa lihat nilai mapel yang diampu
            if ($user->role === 'guru' && $user->subject) {
                $query->where('subject_id', $user->subject);
            }
            // Wali kelas hanya bisa lihat nilai siswa di kelasnya
            if ($user->role === 'wali_kelas' && $user->class) {
                $query->whereHas('student', function ($q) use ($user) {
                    $q->where('rombel_absen', 'like', $user->class . '%');
                });
            }
        }

        return $query->orderBy('updated_at', 'desc')->paginate($filters['per_page'] ?? 100);
    }

    // Membuat nilai baru dengan tracking (guru dipaksa pakai mapel yang diampu)
    public function createGrade($data)
    {
        $user = Auth::user();
        
        // Guru dipaksa input untuk mapel yang diampu
        if ($user->role === 'guru') {
            $data['subject_id'] = $user->subject;
        }

        $data['last_edited_by'] = $user->id;
        $data['last_edited_ip'] = request()->ip();
        $data['last_edited_at'] = now();

        return Grade::create($data);
    }

    // Update nilai dengan role-based access control
    public function updateGrade(Grade $grade, $data)
    {
        $user = Auth::user();

        // Guru hanya bisa edit nilai mapel yang diampu
        if ($user->role === 'guru' && $grade->subject_id != $user->subject) {
            throw new \Exception("Unauthorized to edit this grade");
        }

        $data['last_edited_by'] = $user->id;
        $data['last_edited_ip'] = request()->ip();
        $data['last_edited_at'] = now();

        $grade->update($data);
        return $grade;
    }
}
