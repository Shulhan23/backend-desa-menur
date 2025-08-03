<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Umkm extends Model
{
    protected $fillable = ['nama_umkm', 'produk_jasa', 'jenis_umkm_id', 'no_hp', 'alamat'];

    public function jenis()
    {
        return $this->belongsTo(JenisUmkm::class, 'jenis_umkm_id');
    }
}
