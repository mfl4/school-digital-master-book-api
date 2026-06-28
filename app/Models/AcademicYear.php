<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function gradeSummaries()
    {
        return $this->hasMany(GradeSummary::class);
    }
}
