<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the academic years.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar tahun ajaran berhasil diambil.',
            'data'    => $academicYears
        ]);
    }
}
