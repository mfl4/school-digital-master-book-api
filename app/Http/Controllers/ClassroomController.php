<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    // Mengambil semua data kelas
    public function index()
    {
        $classrooms = Classroom::orderBy('level')->orderBy('major')->orderBy('name')->get();
        return response()->json([
            'status' => 'success',
            'data' => $classrooms
        ]);
    }
}
