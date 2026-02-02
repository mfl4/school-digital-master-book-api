<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Student;

// Request validation untuk update student
class UpdateStudentRequest extends FormRequest
{
    // Authorization check (controller handles logic for Wali Kelas/Admin)
    public function authorize()
    {
        return true;
    }

    // Validation rules (all fields optional untuk update)
    public function rules()
    {
        $nis = $this->route('student'); // NIS dari route parameter

        return [
            'nisn' => 'sometimes|string|max:20|unique:students,nisn,'.$nis.',nis',
            'name' => 'sometimes|string|max:100',
            'gender' => 'sometimes|in:L,P',
            'birth_place' => 'sometimes|string|max:50',
            'birth_date' => 'sometimes|date',
            'religion' => 'sometimes|in:'.implode(',', Student::RELIGIONS),
            'father_name' => 'sometimes|string|max:100',
            'address' => 'sometimes|string',
            'ijazah_number' => 'nullable|string|max:50',
            'rombel_absen' => ['sometimes', 'string', 'max:10', 'regex:/^(X|XI|XII)-\d+-\d+$/'],
        ];
    }
}
