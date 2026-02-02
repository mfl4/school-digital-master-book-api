<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Controller untuk mengelola mata pelajaran (CRUD) oleh admin
class SubjectController extends Controller
{
    // Mengambil daftar semua mata pelajaran dengan pagination
    public function index(Request $request)
    {
        return response()->json(
            Subject::orderBy('name')->paginate($request->input('limit', 10)),
            Response::HTTP_OK
        );
    }

    public function create()
    {
        //
    }

    // Menambahkan mata pelajaran baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:subjects,name',
            'code' => 'required|string|max:20|unique:subjects,code',
        ]);

        // Simpan informasi siapa yang membuat mata pelajaran ini
        $validated['created_by'] = $request->user()->id;

        $subject = Subject::create($validated);

        return response()->json($subject, Response::HTTP_CREATED);
    }

    // Menampilkan detail mata pelajaran berdasarkan ID
    public function show(Subject $subject)
    {
        // Muat relasi creator untuk menampilkan siapa yang membuat mata pelajaran ini
        $subject->load('creator');
        return response()->json($subject, Response::HTTP_OK);
    }

    public function edit(Subject $subject)
    {
        //
    }

    // Mengupdate data mata pelajaran
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:subjects,name,' . $subject->id,
            'code' => 'required|string|max:20|unique:subjects,code,' . $subject->id,
        ]);

        $subject->update($validated);

        return response()->json($subject, Response::HTTP_OK);
    }

    // Menghapus mata pelajaran
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return response()->json([
            'message' => 'Mata pelajaran berhasil dihapus.',
        ], Response::HTTP_OK);
    }
}
