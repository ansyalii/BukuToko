<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpkAlternatif extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'produk_id', 'nama_produk'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}