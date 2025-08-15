<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'nama',
        'kategori',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok',
        'kecepatan_habis',
        'frekuensi_penjualan'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function recalcMetricsWeekly()
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $days = $startDate->diffInDays($endDate) + 1;
        if ($days <= 0)
            $days = 1;

        // total terjual (jenis = 'pemasukan')
        $totalTerjual = DB::table('transaksi')
            ->where('product_id', $this->id)
            ->where('jenis', 'pemasukan')
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('jumlah');

        // frekuensi: jumlah hari berbeda produk terjual
        $freq = DB::table('transaksi')
            ->where('product_id', $this->id)
            ->where('jenis', 'pemasukan')
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('COUNT(DISTINCT DATE(tanggal)) as freq')
            ->value('freq');

        $freq = $freq ? (int) $freq : 0;
        $kecepatan = $totalTerjual / $days;

        $this->kecepatan_habis = round($kecepatan, 4);
        $this->frekuensi_penjualan = $freq;
        $this->save();
    }

}