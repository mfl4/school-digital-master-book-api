<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'level', 'major'];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_classrooms', 'classroom_id', 'student_id', 'id', 'nis')
                    ->withPivot('academic_year_id')
                    ->withTimestamps();
    }

    public function waliKelas()
    {
        return $this->hasOne(User::class, 'classroom_id');
    }
}
