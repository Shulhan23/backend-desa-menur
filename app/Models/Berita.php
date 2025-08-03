<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $fillable = ['judul', 'slug']; // ✅ tambahkan 'slug'

    public function konten()
    {
        return $this->hasMany(KontenBerita::class)->orderBy('urutan');
    }
}
