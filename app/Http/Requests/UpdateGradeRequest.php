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
            'score' => 'required|numeric|min:0|max:100',
        ];
    }
}
