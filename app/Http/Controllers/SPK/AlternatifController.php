<?php

namespace App\Http\Controllers\SPK;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\SpkAlternatif;
use App\Models\SpkNilaiAkhir;

class AlternatifController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $produk = Product::where('user_id', $userId)->get();
        $terpilih = SpkAlternatif::where('user_id', $userId)->pluck('produk_id')->toArray();
        $produkTerpilih = Product::whereIn('id', $terpilih)->get();

        return view('spk.alternatif', compact('produk', 'terpilih', 'produkTerpilih'));
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|array|min:1',
            'produk_id.*' => 'distinct|exists:products,id',
        ]);

        $userId = Auth::id();

        if (!$request->has('produk_id')) {
            return redirect()->back()->with('error', 'Pilih minimal satu produk terlebih dahulu.');
        }

        // Hindari produk_id ganda
        $produkIds = array_unique($request->produk_id);

        // Hapus data lama user
        SpkAlternatif::where('user_id', $userId)->delete();

        // Simpan data baru tanpa duplikat
        foreach ($produkIds as $id) {
            $produk = Product::where('id', $id)->where('user_id', $userId)->first();

            if ($produk) {
                SpkAlternatif::create([
                    'user_id' => $userId,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama,
                ]);
            }
        }

        // Untuk feedback view
        $produk = Product::where('user_id', $userId)->get();
        $terpilih = SpkAlternatif::where('user_id', $userId)->pluck('produk_id')->toArray();
        $produkTerpilih = Product::whereIn('id', $terpilih)->get();

        return view('spk.alternatif', compact('produk', 'terpilih', 'produkTerpilih'))
            ->with('success', 'Produk alternatif berhasil disimpan!');
    }

}