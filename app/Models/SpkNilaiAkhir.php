<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpkNilaiAkhir extends Model
{
    use HasFactory;
    protected $table = 'spk_nilai_akhir';
    protected $fillable = ['user_id', 'alternatif_id', 'kriteria_id', 'nilai'];
}