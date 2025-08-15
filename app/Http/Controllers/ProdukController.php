<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Support\Str;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('user_id', Auth::id());

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('kategori', 'like', "%$search%");
            });
        }

        $produk = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('produk.index', compact('produk'));
    }

    public function create()
    {
        return view('produk.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
        ]);

        // Simpan produk baru
        $product = Product::create([
            'user_id' => Auth::id(),
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'harga_beli' => $request->harga_beli,
            'harga_jual' => $request->harga_jual,
            'stok' => $request->stok,
        ]);

        // Buat kode transaksi pengeluaran: pnglrn + YYYYMMDD + 3 digit urutan
        $today = now()->format('Ymd');
        $count = Transaksi::where('jenis', 'pengeluaran')
                          ->whereDate('created_at', now())
                          ->count() + 1;
        $kode_transaksi = 'pnglrn' . $today . str_pad($count, 3, '0', STR_PAD_LEFT);

        // Buat transaksi pengeluaran otomatis
        $transaksi = Transaksi::create([
            'user_id' => Auth::id(),
            'kode_transaksi' => $kode_transaksi,
            'jenis' => 'pengeluaran',
            'total_harga' => $product->harga_beli * $product->stok,
            'tanggal' => now()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Detail transaksi
        TransaksiDetail::create([
            'transaksi_id' => $transaksi->id,
            'product_id' => $product->id,
            'jumlah' => $product->stok,
            'satuan' => $product->satuan,
            'harga_satuan' => $product->harga_beli,
            'subtotal' => $product->harga_beli * $product->stok,
        ]);

        return redirect()->route('produk.index')
                         ->with('success', 'Produk berhasil ditambahkan dan tercatat di laporan keuangan!');
    }

    public function edit($id)
    {
        $produk = Product::where('id', $id)
                         ->where('user_id', Auth::id())
                         ->firstOrFail();
        return view('produk.edit', compact('produk'));
    }

    public function update(Request $request, $id)
    {
        $produk = Product::where('id', $id)
                         ->where('user_id', Auth::id())
                         ->firstOrFail();

        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
        ]);

        $produk->update([
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'harga_beli' => $request->harga_beli,
            'harga_jual' => $request->harga_jual,
            'stok' => $request->stok,
        ]);

        return redirect()->route('produk.index')
                         ->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $produk = Product::where('id', $id)
                         ->where('user_id', Auth::id())
                         ->firstOrFail();
        $produk->delete();

        return redirect()->route('produk.index')
                         ->with('success', 'Produk berhasil dihapus!');
    }
}