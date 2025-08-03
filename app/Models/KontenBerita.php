<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KontenBerita extends Model
{
    protected $fillable = ['berita_id', 'tipe', 'konten', 'urutan'];

    public function berita()
    {
        return $this->belongsTo(Berita::class);
    }
}

