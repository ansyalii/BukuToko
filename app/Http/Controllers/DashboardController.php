<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaksi;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth()->id();

        $totalTransaksi = Transaksi::where('user_id', $userId)->count();
        $transaksi = Transaksi::with('product')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        $totalProduk = Product::where('user_id', $userId)->count();

        $total_pemasukan = $transaksi->where('jenis', 'pemasukan')->sum('total_harga');
        $total_pengeluaran = $transaksi->where('jenis', 'pengeluaran')->sum('total_harga');

        // Hitung modal hanya untuk pemasukan (biaya beli barang yang dijual)
        $total_modal = $transaksi->where('jenis', 'pemasukan')->sum(function ($trx) {
            return $trx->details->sum(fn($d) => $d->product->harga_beli * $d->jumlah);
        });

        $pendapatan_bersih = $total_pemasukan - $total_modal;


        // Produk Hampir Habis
        $produkMenipis = Product::where('stok', '<=', 5)
            ->where('user_id', $userId)
            ->get();

        // Chart 1: Frekuensi Penjualan Mingguan + Persentase
        $totalFrekuensiMingguan = DB::table('transaksi_details')
            ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
            ->where('transaksis.user_id', $userId)
            ->where('transaksis.jenis', 'pemasukan')
            ->whereBetween('transaksis.created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->distinct(DB::raw('DATE(transaksis.created_at)'))
            ->count();


        $produkChartFrekuensi = DB::table('transaksi_details')
            ->join('products', function ($join) use ($userId) {
                $join->on('transaksi_details.product_id', '=', 'products.id')
                    ->where('products.user_id', $userId);
            })
            ->join('transaksis', function ($join) use ($userId) {
                $join->on('transaksi_details.transaksi_id', '=', 'transaksis.id')
                    ->where('transaksis.user_id', $userId)
                    ->where('transaksis.jenis', 'pemasukan');
            })
            ->whereBetween('transaksis.created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->select('products.nama', DB::raw('COUNT(DISTINCT DATE(transaksis.created_at)) as frekuensi'))
            ->groupBy('products.nama')
            ->orderByDesc('frekuensi')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($totalFrekuensiMingguan) {
                return [
                    'nama' => $item->nama,
                    'frekuensi' => (int) $item->frekuensi,
                    'persentase_frekuensi' => $totalFrekuensiMingguan > 0
                        ? round(($item->frekuensi / $totalFrekuensiMingguan) * 100, 2)
                        : 0
                ];
            });

        // Chart 2: Kecepatan Barang Habis + Persentase
        $totalTerjualMingguan = DB::table('transaksi_details')
            ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
            ->where('transaksis.user_id', $userId)
            ->where('transaksis.jenis', 'pemasukan')
            ->whereBetween('transaksis.created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('transaksi_details.jumlah');


        $produkChartKecepatan = DB::table('transaksi_details')
            ->join('products', function ($join) use ($userId) {
                $join->on('transaksi_details.product_id', '=', 'products.id')
                    ->where('products.user_id', $userId);
            })
            ->join('transaksis', function ($join) use ($userId) {
                $join->on('transaksi_details.transaksi_id', '=', 'transaksis.id')
                    ->where('transaksis.user_id', $userId)
                    ->where('transaksis.jenis', 'pemasukan');
            })
            ->whereBetween('transaksis.created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->select(
                'products.nama',
                DB::raw('SUM(transaksi_details.jumlah) as total_terjual'),
                DB::raw('COUNT(DISTINCT DATE(transaksis.created_at)) as hari_terjual')
            )
            ->groupBy('products.nama')
            ->orderByDesc(DB::raw('SUM(transaksi_details.jumlah) / NULLIF(COUNT(DISTINCT DATE(transaksis.created_at)),0)'))
            ->limit(5)
            ->get()
            ->map(function ($item) use ($totalTerjualMingguan) {
                $kecepatan = $item->hari_terjual > 0
                    ? $item->total_terjual / $item->hari_terjual
                    : 0;
                return [
                    'nama' => $item->nama,
                    'kecepatan' => round($kecepatan, 2),
                    'persentase_terjual' => $totalTerjualMingguan > 0
                        ? round(($item->total_terjual / $totalTerjualMingguan) * 100, 2)
                        : 0
                ];
            });


        return view('dashboard.index', compact(
            'totalTransaksi',
            'totalProduk',
            'total_pemasukan',
            'total_pengeluaran',
            'pendapatan_bersih',
            'produkMenipis',
            'produkChartFrekuensi',
            'produkChartKecepatan'
        ));
    }
}