<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Request validation untuk create grade
class StoreGradeRequest extends FormRequest
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
            'student_id' => 'required|exists:students,nis',
            'subject_id' => 'sometimes|exists:subjects,id',
            'semester'   => 'required|string',
            'score'      => 'required|numeric|min:0|max:100',
        ];
    }

    // Custom validation for unique constraint
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $subjectId = $this->input('subject_id') ?? $this->user()->subject;
            
            if ($subjectId && $this->input('student_id') && $this->input('semester')) {
                $exists = \App\Models\Grade::where('student_id', $this->input('student_id'))
                    ->where('subject_id', $subjectId)
                    ->where('semester', $this->input('semester'))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('student_id', 'Nilai untuk siswa pada mata pelajaran dan semester ini sudah ada.');
                }
            }
        });
    }
}
