<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Berita;
use App\Models\KontenBerita;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Support\Str;

class BeritaController extends Controller
{
    public function index()
    {
        return Berita::with('konten')->latest()->get();
    }

    public function showById($id)
    {
    $berita = Berita::with('konten')->findOrFail($id);

    return response()->json($berita);
    }

    public function showBySlug($slug)
    {
        $berita = \App\Models\Berita::with('konten')->where('slug', $slug)->first();

        if (!$berita) {
            return response()->json(['message' => 'Berita tidak ditemukan.'], 404);
        }

        return response()->json($berita);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|array',
            'konten.*.tipe' => 'required|in:teks,gambar',
            'konten.*.konten' => 'required',
        ]);

        DB::beginTransaction();
        try {
            // Buat slug unik dari judul
            $slug = Str::slug($validated['judul']);
            $slugAsli = $slug;
            $i = 1;
            while (Berita::where('slug', $slug)->exists()) {
                $slug = $slugAsli . '-' . $i++;
            }

            $berita = Berita::create([
                'judul' => $validated['judul'],
                'slug' => $slug
            ]);

            foreach ($validated['konten'] as $index => $item) {
                $kontenValue = $item['konten'];

                if ($item['tipe'] === 'teks') {
                    $kontenValue = Purifier::clean($kontenValue);
                }

                if ($item['tipe'] === 'gambar' && $request->hasFile("konten.$index.konten")) {
                    $file = $request->file("konten.$index.konten");
                    $kontenValue = $file->store('berita', 'public');
                }

                KontenBerita::create([
                    'berita_id' => $berita->id,
                    'tipe' => $item['tipe'],
                    'konten' => $kontenValue,
                    'urutan' => $index + 1
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Berita berhasil disimpan',
                'data' => $berita->load('konten')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal menyimpan berita',
                'details' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $berita = Berita::with('konten')->findOrFail($id);

        // Hapus konten berdasarkan permintaan eksplisit dari frontend
        if ($request->has('hapus_konten')) {
            foreach ($request->hapus_konten as $kontenId) {
                $konten = KontenBerita::find($kontenId);
                if ($konten && $konten->tipe === 'gambar') {
                    Storage::disk('public')->delete($konten->konten);
                }
                $konten?->delete();
            }
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|array',
            'konten.*.tipe' => 'required|in:teks,gambar',
            'konten.*.id' => 'nullable|integer',
            // konten.*.konten divalidasi manual tergantung tipenya
        ]);

        $berita->update([
        'judul' => $validated['judul'],
        'slug' => Str::slug($validated['judul'])
        ]);

        $processedKontenIds = [];

        foreach ($validated['konten'] as $index => $item) {
            $kontenId = $item['id'] ?? null;
            $tipe = $item['tipe'];
            $kontenValue = null;

            // Ambil konten tergantung tipe
            if ($tipe === 'teks') {
                $kontenValue = Purifier::clean($request->input("konten.$index.konten", ''));
            }elseif ($tipe === 'gambar') {
                if ($request->hasFile("konten.$index.konten")) {
                    // Upload gambar baru
                    $file = $request->file("konten.$index.konten");
                    $kontenValue = $file->store('berita', 'public');

                    // Hapus file lama jika ada
                    if ($kontenId) {
                        $kontenLama = KontenBerita::find($kontenId);
                        if ($kontenLama && $kontenLama->tipe === 'gambar') {
                            Storage::disk('public')->delete($kontenLama->konten);
                        }
                    }
                } else {
                    // Jika tidak upload ulang, ambil string path gambar dari input biasa
                    $kontenValue = $request->input("konten.$index.konten");
                }
            }

                if ($kontenId) {
                    $konten = KontenBerita::find($kontenId);
                    if ($konten) {
                        // Jangan timpa konten kalau tidak upload ulang / tidak edit teks
                        $updateData = [
                            'tipe' => $tipe,
                            'urutan' => $index + 1,
                        ];

                        // Hanya isi konten kalau ada nilai baru
                        if (!is_null($kontenValue) && $kontenValue !== '') {
                            $updateData['konten'] = $kontenValue;
                        }

                        $konten->update($updateData);
                        $processedKontenIds[] = $konten->id;
                    }
                } else {
                // Tambah konten baru
                $konten = KontenBerita::create([
                    'berita_id' => $berita->id,
                    'tipe' => $tipe,
                    'konten' => $kontenValue,
                    'urutan' => $index + 1,
                ]);
                $processedKontenIds[] = $konten->id;
            }
        }

        // Hapus konten yang tidak ada di data terbaru
        KontenBerita::where('berita_id', $berita->id)
            ->whereNotIn('id', $processedKontenIds)
            ->each(function ($konten) {
                if ($konten->tipe === 'gambar') {
                    Storage::disk('public')->delete($konten->konten);
                }
                $konten->delete();
            });

        return response()->json(['message' => 'Berita berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $berita = Berita::findOrFail($id);

        DB::beginTransaction();
        try {
            foreach ($berita->konten as $item) {
                if ($item->tipe === 'gambar' && $item->konten && Storage::disk('public')->exists($item->konten)) {
                    Storage::disk('public')->delete($item->konten);
                }
                $item->delete();
            }

            $berita->delete();

            DB::commit();
            return response()->json(['message' => 'Berita berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal menghapus berita',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
