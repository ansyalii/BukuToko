<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaksi;
use App\Models\Product;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $totalTransaksi = Transaksi::where('user_id', $userId)->count();
        $transaksi = Transaksi::with('product')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        $totalProduk = Product::where('user_id', $userId)->count();

        $total_pemasukan = $transaksi->where('jenis', 'pemasukan')->sum('total_harga');
        $total_pengeluaran = $transaksi->where('jenis', 'pengeluaran')->sum('total_harga');

        // Hitung modal hanya untuk pemasukan
        $total_modal = $transaksi->where('jenis', 'pemasukan')->sum(function ($trx) {
            return $trx->details->sum(fn($d) => $d->product->harga_beli * $d->jumlah);
        });

        $pendapatan_bersih = $total_pemasukan - $total_modal;

        // Produk Hampir Habis
        $produkMenipis = Product::where('stok', '<=', 5)
            ->where('user_id', $userId)
            ->get();

        // Tentukan range minggu ini
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        // Ambil semua transaksi pemasukan minggu ini
        $transaksiMingguan = DB::table('transaksi_details')
            ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
            ->where('transaksis.user_id', $userId)
            ->where('transaksis.jenis', 'pemasukan')
            ->whereBetween('transaksis.created_at', [$startOfWeek, $endOfWeek])
            ->select('transaksi_details.*', 'transaksis.created_at')
            ->get();

        // Update frekuensi_penjualan per produk
        $produkFrekuensi = $transaksiMingguan
            ->groupBy('product_id')
            ->map(function ($items, $productId) {
                $itemsArray = $items->toArray(); // Konversi ke array
                $frekuensi = count(array_unique(array_map(fn($t) => date('Y-m-d', strtotime($t->created_at)), $itemsArray)));
                return ['product_id' => $productId, 'frekuensi' => $frekuensi];
            });

        foreach ($produkFrekuensi as $data) {
            Product::where('id', $data['product_id'])
                ->update(['frekuensi_penjualan' => $data['frekuensi']]);
        }

        // Update kecepatan_habis per produk
        $produkKecepatan = $transaksiMingguan
            ->groupBy('product_id')
            ->map(function ($items, $productId) {
                $itemsArray = $items->toArray(); // Konversi ke array
                $totalTerjual = array_sum(array_map(fn($t) => $t->jumlah, $itemsArray));
                $hariTerjual = count(array_unique(array_map(fn($t) => date('Y-m-d', strtotime($t->created_at)), $itemsArray)));
                $kecepatan = $hariTerjual > 0 ? $totalTerjual / $hariTerjual : 0;
                return ['product_id' => $productId, 'kecepatan' => round($kecepatan, 2)];
            });

        foreach ($produkKecepatan as $data) {
            Product::where('id', $data['product_id'])
                ->update(['kecepatan_habis' => $data['kecepatan']]);
        }

        // Ambil data chart dari tabel products
        $produkChartFrekuensi = Product::where('user_id', $userId)
            ->orderByDesc('frekuensi_penjualan')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($transaksiMingguan) {
                $totalFrekuensi = $transaksiMingguan->count();
                return [
                    'nama' => $item->nama,
                    'frekuensi' => $item->frekuensi_penjualan,
                    'persentase_frekuensi' => $totalFrekuensi > 0
                        ? round(($item->frekuensi_penjualan / $totalFrekuensi) * 100, 2)
                        : 0
                ];
            });

        $produkChartKecepatan = Product::where('user_id', $userId)
            ->orderByDesc('kecepatan_habis')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($transaksiMingguan) {
                $totalTerjual = $transaksiMingguan->sum('jumlah');
                return [
                    'nama' => $item->nama,
                    'kecepatan' => $item->kecepatan_habis,
                    'persentase_terjual' => $totalTerjual > 0
                        ? round(($item->kecepatan_habis / $totalTerjual) * 100, 2)
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