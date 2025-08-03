<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisUmkm;

class JenisUmkmController extends Controller
{
    public function index()
    {
        return JenisUmkm::all();
    }

    public function showBySlug($slug)
    {
    $jenis = JenisUMKM::where('slug', $slug)->firstOrFail();

    if (!$jenis) {
        return response()->json(['message' => 'Jenis UMKM tidak ditemukan'], 404);
    }

    $umkms = UMKM::where('jenis_umkm_id', $jenis->id)->get();

    return response()->json([
        'jenis' => $jenis,
        'umkms' => $umkms,
    ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:255|unique:jenis_umkm,nama_jenis',
        ]);

        $jenis = JenisUmkm::create(['nama_jenis' => $request->nama_jenis]);
        return response()->json($jenis, 201);
    }

    public function destroy($id)
    {
        $jenis = JenisUmkm::findOrFail($id);
        $jenis->delete();

    return response()->json(['message' => 'Jenis UMKM berhasil dihapus']);
    }

    public function update(Request $request, $id)
    {
    $jenis = JenisUmkm::findOrFail($id);
    $jenis->update($request->validate([
        'nama_jenis' => 'required|string|max:255',
    ]));

    return response()->json(['message' => 'Berhasil update', 'data' => $jenis]);
    }


}

