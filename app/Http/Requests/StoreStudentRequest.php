<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Student;

// Request validation untuk create student
class StoreStudentRequest extends FormRequest
{
    // Authorization check
    public function authorize()
    {
        return true;
    }

    // Validation rules
    public function rules()
    {
        return [
            'nis' => 'required|string|max:20|unique:students,nis',
            'nisn' => 'required|string|max:20|unique:students,nisn',
            'name' => 'required|string|max:100',
            'gender' => 'required|in:L,P',
            'birth_place' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'religion' => 'required|in:'.implode(',', Student::RELIGIONS),
            'father_name' => 'required|string|max:100',
            'address' => 'required|string',
            'ijazah_number' => 'nullable|string|max:50',
            'rombel_absen' => ['required', 'string', 'max:10', 'regex:/^(X|XI|XII)-\d+-\d+$/'],
        ];
    }
}
