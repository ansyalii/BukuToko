<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Transaksi;
use Carbon\Carbon;

class RecalcProductMetrics extends Command
{
    protected $signature = 'metrics:recalc-weekly';
    protected $description = 'Recalculate product sales frequency and speed every week';

    public function handle()
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $products = Product::all();

        foreach ($products as $product) {
            $totalTerjual = Transaksi::where('product_id', $product->id)
                ->where('jenis', 'pemasukan')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('jumlah');

            $frekuensi = Transaksi::where('product_id', $product->id)
                ->where('jenis', 'pemasukan')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('COUNT(DISTINCT DATE(created_at)) as freq')
                ->value('freq');

            $days = $startDate->diffInDays($endDate) + 1;
            $kecepatan = $days > 0 ? $totalTerjual / $days : 0;

            $product->update([
                'kecepatan_habis' => round($kecepatan, 4),
                'frekuensi_penjualan' => $frekuensi ?? 0,
            ]);
        }

        $this->info('Weekly product metrics recalculated successfully.');
    }
}