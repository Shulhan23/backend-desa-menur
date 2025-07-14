<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BeritaController extends Controller
{
    public function index()
    {
        return Berita::latest()->get();
    }

    public function show($id)
    {
        return Berita::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
         'judul' => 'required|string|max:255',
         'isi' => 'required|string',
         'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('berita', 'public');
        }

        return Berita::create($data);
    }

    public function destroy($id)
    {
        $berita = Berita::findOrFail($id);
        if ($berita->gambar) {
            Storage::disk('public')->delete($berita->gambar);
        }
        $berita->delete();
        return response()->json(['message' => 'Berita dihapus']);
    }
}

