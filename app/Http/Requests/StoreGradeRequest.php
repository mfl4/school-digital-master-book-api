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
}
