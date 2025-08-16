<?php

namespace App\Http\Controllers\SPK;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SpkKriteria;
use App\Models\Product;
use App\Models\SpkAlternatif;
use App\Models\SpkNilaiAkhir;
use Illuminate\Support\Facades\DB;

class SPKController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $bobot = SpkKriteria::where('user_id', $userId)->get();
        $hasil = $this->hitungWP() ?? []; // pastikan selalu array

        return view('spk.index', compact('bobot', 'hasil'));
    }

    public function hasilWP()
    {
        $userId = Auth::id();
        $bobot = SpkKriteria::where('user_id', $userId)->get();
        $hasil = $this->hitungWP();

        if ($hasil === null) {
            $hasil = [];
            return redirect()->route('spk.index')->with('error', 'Bobot kriteria belum ada, silakan isi bobot AHP.');
        }

        return view('spk.index', compact('bobot', 'hasil'));
    }

    private function hitungWP()
    {
        $userId = Auth::id();

        $alternatifList = SpkAlternatif::where('user_id', $userId)
            ->groupBy('produk_id')
            ->selectRaw('MIN(id) as id, produk_id')
            ->get();

        $bobot = SpkKriteria::where('user_id', $userId)->pluck('bobot')->toArray();

        if (count($bobot) !== 4) {
            return null;
        }

        $hasil = [];

        foreach ($alternatifList as $alt) {
            $produk = Product::where('id', $alt->produk_id)
                ->where('user_id', $userId)
                ->first();

            if (!$produk)
                continue;

            // Ambil data transaksi sama seperti di dashboard
            $transaksi = DB::table('transaksi_details')
                ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
                ->where('transaksi_details.product_id', $produk->id)
                ->where('transaksis.user_id', $userId)
                ->where('transaksis.jenis', 'pemasukan')
                ->whereBetween('transaksis.created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->select(
                    DB::raw('SUM(transaksi_details.jumlah) as total_terjual'),
                    DB::raw('COUNT(DISTINCT DATE(transaksis.created_at)) as hari_terjual')
                )
                ->first();

            $frekuensi = DB::table('transaksi_details')
                ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
                ->where('transaksi_details.product_id', $produk->id)
                ->where('transaksis.user_id', $userId)
                ->where('transaksis.jenis', 'pemasukan')
                ->whereBetween('transaksis.created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->distinct()
                ->count(DB::raw('DATE(transaksis.created_at)'));

            // Kecepatan sama seperti di dashboard (total terjual dibagi hari terjual)
            $kecepatan = $transaksi->hari_terjual > 0
                ? $transaksi->total_terjual / $transaksi->hari_terjual
                : 0;

            $keuntungan = floatval(($produk->harga_jual ?? 0) - ($produk->harga_beli ?? 0));
            $hargaBeli = floatval($produk->harga_beli ?? 1);

            $nilai = [$frekuensi, $keuntungan, $kecepatan, $hargaBeli];
            $skor = 1;

            foreach ($nilai as $i => $v) {
                $bobot_i = $i === 3 ? -$bobot[$i] : $bobot[$i]; // harga dianggap cost
                $skor *= pow(max($v, 1), $bobot_i);
            }

            $hasil[] = [
                'alternatif_id' => $alt->id,
                'produk_id' => $produk->id,
                'nama' => $produk->nama,
                'frekuensi' => $frekuensi,
                'kecepatan' => round($kecepatan, 2),
                'keuntungan' => $keuntungan,
                'harga_beli' => $hargaBeli,
                'skor' => $skor,
            ];
        }

        $totalSkor = array_sum(array_column($hasil, 'skor'));

        foreach ($hasil as &$row) {
            $row['nilai_normalisasi'] = $totalSkor > 0 ? round($row['skor'] / $totalSkor, 3) : 0;
        }

        usort($hasil, fn($a, $b) => $b['nilai_normalisasi'] <=> $a['nilai_normalisasi']);

        return collect($hasil)->unique('produk_id')->sortByDesc('nilai_normalisasi')->values()->all();
    }

    public function reset()
    {
        $userId = auth()->id();

        SpkAlternatif::where('user_id', $userId)->delete();
        SpkNilaiAkhir::where('user_id', $userId)->delete();
        SpkKriteria::where('user_id', $userId)->update(['bobot' => 0]);
        session()->forget('spk_hasil');

        return redirect()->route('spk.index')->with('success', 'Perhitungan SPK dan bobot AHP berhasil direset, kriteria tetap ada.');
    }
}