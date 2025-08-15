<?php

namespace App\Http\Controllers\SPK;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\SpkKriteria;

class KriteriaController extends Controller
{
    public function index()
    {
        return view('spk.kriteria');
    }

    public function proses(Request $request)
    {
        // Definisikan kriteria
        $kriteria = [
            'Frekuensi Penjualan',
            'Keuntungan per Item',
            'Kecepatan Barang Habis',
            'Harga dari Supplier'
        ];

        $n = count($kriteria);
        $userId = Auth::id();

        // Aturan validasi untuk perbandingan berpasangan
        $rules = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $rules["perbandingan.$i.$j"] = 'required|numeric|min:0.111|max:9';
            }
        }

        $messages = [
            'required' => 'Perbandingan antar kriteria wajib diisi.',
            'numeric' => 'Nilai harus berupa angka.',
            'min' => 'Nilai minimal adalah 0.111 (setara 1/9).',
            'max' => 'Nilai maksimal adalah 9.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Bangun matriks perbandingan berpasangan
        $matrix = array_fill(0, $n, array_fill(0, $n, 1.0));

        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $value = floatval($request->input("perbandingan.$i.$j"));
                $matrix[$i][$j] = $value;
                $matrix[$j][$i] = 1.0 / $value;
            }
        }

        // Hitung bobot AHP
        $result = $this->hitungBobotAHP($matrix);

        $bobot = $result['bobot'];
        $cr = $result['cr'];

        // Cek konsistensi
        if ($cr > 0.1) {
            return redirect()->back()
                ->withErrors([
                    "Konsistensi rendah! Nilai Consistency Ratio (CR) = " . round($cr, 3) .
                    " (> 0.1). Harap sesuaikan penilaian agar lebih konsisten."
                ])
                ->withInput();
        }

        // Simpan ke database
        SpkKriteria::where('user_id', $userId)->delete();

        foreach ($kriteria as $i => $nama) {
            SpkKriteria::create([
                'user_id' => $userId,
                'nama_kriteria' => $nama,
                'bobot' => $bobot[$i]
            ]);
        }

        return redirect()->route('spk.index')->with('success', 'Kriteria berhasil dihitung dan disimpan!');
    }

    /**
     * Hitung bobot AHP dengan normalisasi kolom dan cek konsistensi
     */
    private function hitungBobotAHP($matrix)
    {
        $n = count($matrix);

        // Hitung jumlah setiap kolom
        $jumlahKolom = array_fill(0, $n, 0);
        for ($j = 0; $j < $n; $j++) {
            for ($i = 0; $i < $n; $i++) {
                $jumlahKolom[$j] += $matrix[$i][$j];
            }
        }

        // Normalisasi matriks
        $normal = [];
        for ($i = 0; $i < $n; $i++) {
            $normal[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $normal[$i][$j] = $matrix[$i][$j] / $jumlahKolom[$j];
            }
        }

        // Bobot = rata-rata baris dari matriks ternormalisasi
        $bobot = [];
        for ($i = 0; $i < $n; $i++) {
            $bobot[$i] = array_sum($normal[$i]) / $n;
        }

        // Normalisasi bobot agar total = 1 (opsional, tapi baik)
        $totalBobot = array_sum($bobot);
        foreach ($bobot as &$b) {
            $b = round($b / $totalBobot, 4);
        }

        // Hitung λ_max untuk uji konsistensi
        $lambdaMax = 0;
        for ($i = 0; $i < $n; $i++) {
            $sum = 0;
            for ($j = 0; $j < $n; $j++) {
                $sum += $matrix[$i][$j] * $bobot[$j];
            }
            $lambdaMax += $sum / $bobot[$i];
        }
        $lambdaMax /= $n;

        // CI = (λ_max - n) / (n - 1)
        $ci = ($lambdaMax - $n) / ($n - 1);

        // RI untuk n=4 adalah 0.90
        $randomIndex = [0, 0, 0, 0.58, 0.90, 1.12, 1.24, 1.32, 1.41]; // n=1 sampai 8
        $ri = isset($randomIndex[$n]) ? $randomIndex[$n] : 1.41;

        $cr = $ri == 0 ? 0 : $ci / $ri;

        return [
            'bobot' => $bobot,
            'cr' => $cr,
            'ci' => $ci,
            'lambda_max' => $lambdaMax
        ];
    }
}