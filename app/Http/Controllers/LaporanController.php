<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Transaksi;
use App\Models\Product;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with('details.product')
            ->where('user_id', Auth::id());

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal', [$request->dari, $request->sampai]);
        }

        // Urut berdasarkan tanggal terbaru terlebih dahulu
        $transaksi = $query->orderBy('tanggal', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Total pemasukan / pengeluaran / pendapatan sesuai filter
        $total_pemasukan = Transaksi::where('user_id', Auth::id())
            ->when(
                $request->filled('dari') && $request->filled('sampai'),
                fn($q) =>
                $q->whereBetween('tanggal', [$request->dari, $request->sampai])
            )
            ->where('jenis', 'pemasukan')
            ->sum('total_harga');

        $total_pengeluaran = Transaksi::where('user_id', Auth::id())
            ->when(
                $request->filled('dari') && $request->filled('sampai'),
                fn($q) =>
                $q->whereBetween('tanggal', [$request->dari, $request->sampai])
            )
            ->where('jenis', 'pengeluaran')
            ->sum('total_harga');

        $pendapatan = $total_pemasukan - $total_pengeluaran;

        return view('laporan.index', compact('transaksi', 'total_pemasukan', 'total_pengeluaran', 'pendapatan'));
    }


    public function transaksi(Request $request)
    {
        // Panggil otomatis buat saldo pindahan jika tanggal 1
        $this->buatSaldoPindahan();

        $today = date('Y-m-d');
        $produks = Product::select('id', 'nama', 'satuan', 'harga_jual', 'harga_beli')
            ->where('user_id', Auth::id())
            ->get();

        $transaksi = Transaksi::whereDate('tanggal', $today)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('laporan.transaksi', compact('produks', 'transaksi'));
    }

    private function buatSaldoPindahan()
    {
        $today = Carbon::now();
        $user_id = Auth::id();

        // Hanya eksekusi tanggal 1
        if ($today->day != 1) return;

        // Cek apakah saldo pindahan bulan ini sudah ada
        $saldoExist = Transaksi::where('user_id', $user_id)
            ->where('jenis', 'pemasukan')
            ->whereDate('tanggal', $today->format('Y-m-d'))
            ->where('deskripsi', 'like', '%Saldo Pindahan%')
            ->exists();

        if ($saldoExist) return; // sudah ada, tidak buat lagi

        // Hitung saldo bulan sebelumnya
        $startPrevMonth = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $endPrevMonth = $today->copy()->subMonthNoOverflow()->endOfMonth();

        $totalPemasukan = Transaksi::where('user_id', $user_id)
            ->where('jenis', 'pemasukan')
            ->whereBetween('tanggal', [$startPrevMonth, $endPrevMonth])
            ->sum('total_harga');

        $totalPengeluaran = Transaksi::where('user_id', $user_id)
            ->where('jenis', 'pengeluaran')
            ->whereBetween('tanggal', [$startPrevMonth, $endPrevMonth])
            ->sum('total_harga');

        $saldo = $totalPemasukan - $totalPengeluaran;

        if ($saldo <= 0) return; // tidak buat saldo jika negatif atau nol

        // Buat transaksi saldo pindahan
        Transaksi::create([
            'kode_transaksi' => 'saldo' . $today->format('Ymd'),
            'user_id' => $user_id,
            'jenis' => 'pemasukan',
            'tanggal' => $today->format('Y-m-d'),
            'deskripsi' => 'Saldo Pindahan Bulan Lalu',
            'total_harga' => $saldo,
        ]);
    }
    private function generateKodeTransaksi($jenis)
    {
        $prefix = $jenis === 'pemasukan' ? 'pmskn' : 'pnglrn';
        $today = now()->format('Ymd'); // ambil tahun-bulan-tanggal sekarang

        // Cari transaksi terakhir hari ini
        $lastTransaksi = Transaksi::where('jenis', $jenis)
            ->where('kode_transaksi', 'like', $prefix . $today . '%')
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = 0;
        if ($lastTransaksi && preg_match('/(\d{3})$/', $lastTransaksi->kode_transaksi, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $today . $newNumber;
    }

    private function recalcAllProductMetricsWeekly()
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $products = Product::where('user_id', Auth::id())->get();

        foreach ($products as $product) {
            // total terjual dari transaksi_detail
            $totalTerjual = TransaksiDetail::where('product_id', $product->id)
                ->whereHas('transaksi', fn($q) => $q->where('jenis', 'pemasukan')
                    ->whereBetween('created_at', [$startDate, $endDate]))
                ->sum('jumlah');

            // frekuensi penjualan: jumlah hari unik transaksi
            $frekuensi = TransaksiDetail::where('product_id', $product->id)
                ->whereHas('transaksi', fn($q) => $q->where('jenis', 'pemasukan')
                    ->whereBetween('created_at', [$startDate, $endDate]))
                ->selectRaw('COUNT(DISTINCT DATE(created_at)) as freq')
                ->value('freq');

            $days = $startDate->diffInDays($endDate) + 1;
            $kecepatan = $days > 0 ? $totalTerjual / $days : 0;

            $product->update([
                'kecepatan_habis' => round($kecepatan, 4),
                'frekuensi_penjualan' => $frekuensi ?? 0,
            ]);
        }
    }


    public function store(Request $request)
{
    $request->validate([
        'jenis' => 'required|in:pemasukan,pengeluaran',
        'tanggal' => 'required|date',
        'deskripsi' => 'required|string',
        'qty' => 'required|array',
        'qty.*' => 'required|numeric|min:0.01',
        'satuan' => 'required|array',
        'satuan.*' => 'required|string',
    ]);

    $jenis = $request->jenis;
    $tanggal = $request->tanggal;
    $deskripsi = $request->deskripsi;
    $user_id = Auth::id();

    $produk_ids = $request->input('produk_id', []); // bisa kosong untuk produk baru
    $nama_baru = $request->input('produk_nama_baru', []); // array produk baru
    $harga_baru = $request->input('harga_baru', []); // array harga produk baru
    $qtys = $request->qty;
    $satuans = $request->satuan;

    // Buat kode transaksi unik
    $kode_transaksi = $this->generateKodeTransaksi($jenis);

    // Buat transaksi utama
    $transaksi = Transaksi::create([
        'kode_transaksi' => $kode_transaksi,
        'user_id' => $user_id,
        'jenis' => $jenis,
        'tanggal' => $tanggal,
        'deskripsi' => $deskripsi,
        'total_harga' => 0, // nanti dijumlahkan dari detail
    ]);

    $total = 0;
    foreach ($qtys as $index => $qty) {
        $produk_id = $produk_ids[$index] ?? null;
        $satuan = $satuans[$index];

        // Jika produk baru, simpan dulu ke products
        if (!$produk_id && isset($nama_baru[$index], $harga_baru[$index])) {
            $produk = Product::create([
                'user_id' => $user_id,
                'nama' => $nama_baru[$index],
                'satuan' => $satuan,
                'harga_jual' => $harga_baru[$index],
                'harga_beli' => $harga_baru[$index],
                'stok' => 0,
            ]);
            $produk_id = $produk->id;
        } else {
            $produk = Product::find($produk_id);
            if (!$produk) continue;
        }

        $harga = $jenis === 'pemasukan' ? $produk->harga_jual : $produk->harga_beli;
        $subtotal = $harga * $qty;
        $total += $subtotal;

        // Buat transaksi detail
        TransaksiDetail::create([
            'transaksi_id' => $transaksi->id,
            'product_id' => $produk->id,
            'jumlah' => $qty,
            'subtotal' => $subtotal,
            'deskripsi' => ($jenis === 'pemasukan' ? "Penjualan" : "Pembelian") . ": $produk->nama $qty $satuan",
        ]);

        // Update stok
        if ($jenis === 'pemasukan') {
            $produk->stok -= $qty;
        } else {
            $produk->stok += $qty;
        }
        $produk->save();
    }

    // Update total_harga transaksi utama
    $transaksi->update(['total_harga' => $total]);

    $this->recalcAllProductMetricsWeekly();

    return redirect()->route('laporan.transaksi')->with('success', 'Transaksi berhasil disimpan.');
}
    public function print(Request $request)
    {
        $query = Transaksi::with('details.product');

        if ($request->filled('dari'))
            $query->whereDate('created_at', '>=', $request->dari);
        if ($request->filled('sampai'))
            $query->whereDate('created_at', '<=', $request->sampai);

        $transaksi = $query->orderBy('tanggal', 'asc')->get();

        $total_penjualan = $transaksi->sum('total_harga');
        $total_modal = $transaksi->sum(function ($trx) {
            return $trx->details->sum(fn($d) => $d->product->harga_beli * $d->jumlah);
        });
        $laba = $total_penjualan - $total_modal;

        return view('laporan.print', compact('transaksi', 'total_penjualan', 'total_modal', 'laba'));
    }

}