<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisUmkm extends Model
{

    protected $table = 'jenis_umkm';

    protected $fillable = ['nama_jenis'];

    public function umkms()
    {
        return $this->hasMany(Umkm::class);
    }

    protected static function booted()
    {
        static::creating(function ($jenis) {
            $jenis->slug = Str::slug($jenis->nama_jenis);
        });

        static::updating(function ($jenis) {
            $jenis->slug = Str::slug($jenis->nama_jenis);
        });
    }

}
