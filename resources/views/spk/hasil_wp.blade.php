@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Hasil Perhitungan Metode Weighted Product</h2>

        <table class="w-full table-auto border border-collapse mb-6">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">#</th>
                    <th class="border p-2">Produk</th>
                    <th class="border p-2">Stok</th>
                    <th class="border p-2">Penjualan</th>
                    <th class="border p-2">Keuntungan / Unit</th>
                    <th class="border p-2">Harga Beli</th>
                    <th class="border p-2">Nilai WP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hasil as $i => $item)
                    <tr>
                        <td class="border p-2 text-center">{{ $i + 1 }}</td>
                        <td class="border p-2 font-semibold">{{ $item['nama'] }}</td>
                        <td class="border p-2 text-center">{{ $item['stok'] }}</td>
                        <td class="border p-2 text-center">{{ $item['penjualan'] }}</td>
                        <td class="border p-2 text-center">Rp{{ number_format($item['keuntungan']) }}</td>
                        <td class="border p-2 text-center">Rp{{ number_format($item['harga_beli']) }}</td>
                        <td class="border p-2 text-center text-blue-700 font-bold">{{ $item['nilai_normalisasi'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('spk.index') }}"
            class="inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            ⬅ Kembali ke Halaman SPK
        </a>
    </div>
@endsection