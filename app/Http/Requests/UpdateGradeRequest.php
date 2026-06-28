<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Request validation untuk update grade
class UpdateGradeRequest extends FormRequest
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
            'student_id' => 'sometimes|exists:students,nis',
            'subject_id' => 'sometimes|exists:subjects,id',
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'semester'   => 'sometimes|in:odd,even',
            'score'      => 'sometimes|numeric|min:0|max:100',
        ];
    }
}
