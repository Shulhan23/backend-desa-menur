<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Umkm;
use App\Models\JenisUmkm;

class UmkmController extends Controller
{
    public function index(Request $request)
    {
        $query = Umkm::with('jenis');

        if ($request->has('jenis')) {
            $query->where('jenis_umkm_id', $request->jenis);
        }

        return response()->json([
            'data' => $query->get()
        ]);
    }

    public function show($id)
    {
        $umkm = Umkm::with('jenis')->findOrFail($id);
        return response()->json($umkm);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_umkm' => 'required|string|max:255',
            'produk_jasa' => 'required|string|max:255',
            'jenis_umkm_id' => 'required|exists:jenis_umkm,id',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string',
        ]);

        $umkm = Umkm::create($validated);
        return response()->json($umkm, 201);
    }

    public function update(Request $request, $id)
    {
        $umkm = Umkm::findOrFail($id);

        $validated = $request->validate([
            'nama_umkm' => 'required|string|max:255',
            'produk_jasa' => 'required|string|max:255',
            'jenis_umkm_id' => 'required|exists:jenis_umkm,id',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string',
        ]);

        $umkm->update($validated);
        return response()->json($umkm);
    }

    public function destroy($id)
    {
        $umkm = Umkm::findOrFail($id);
        $umkm->delete();

        return response()->json(['message' => 'UMKM berhasil dihapus']);
    }
}
