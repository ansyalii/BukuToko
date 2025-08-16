@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-500 text-white p-6 rounded-2xl shadow mb-10 text-center">
            <h2 class="text-3xl font-bold mb-2">Sistem Pendukung Keputusan (SPK)</h2>
            <p class="text-sm">Bantu pemilik UMKM menentukan produk terbaik untuk pembelian ulang</p>
            <form action="{{ route('spk.reset') }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded shadow">
                    Reset SPK
                </button>
            </form>
        </div>

        <!-- Grid 3 kolom, Alternatif Produk disamping Kriteria -->
        <div class="flex grid-col md:flex-row gap-6 mb-8">
            <!-- Kriteria AHP -->
            <div class="md:basis-1/3 bg-white rounded-2xl shadow hover:shadow-lg transition p-6 text-center">
                <div class="text-indigo-600 text-4xl mb-4">
                    <i class="bi bi-sliders2-vertical"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Kriteria AHP</h3>
                <p class="text-sm text-gray-600 mb-4">Masukkan dan hitung bobot kriteria menggunakan AHP.</p>
                <a href="{{ route('spk.kriteria') }}"
                    class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                    Kelola Kriteria
                </a>

                @if ($bobot->count())
                    <div class="mt-10 bg-white rounded-2xl shadow p-6">
                        <h3 class="text-xl font-bold mb-4 text-gray-800">Bobot Kriteria (AHP)</h3>
                        <table class="w-full text-left border border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2">No</th>
                                    <th class="border px-4 py-2">Nama Kriteria</th>
                                    <th class="border px-4 py-2">Bobot</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bobot as $i => $item)
                                    <tr class="{{ $i % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                        <td class="border px-4 py-2">{{ $i + 1 }}</td>
                                        <td class="border px-4 py-2">{{ $item->nama_kriteria }}</td>
                                        <td class="border px-4 py-2">{{ number_format($item->bobot, 3) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Alternatif Produk -->
            <div class="md:basis-2/3 bg-white rounded-2xl shadow hover:shadow-lg transition p-6 text-center">
                <div class="text-green-600 text-4xl mb-4">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Alternatif Produk</h3>
                <p class="text-sm text-gray-600 mb-4">Pilih produk yang akan dibandingkan dan dinilai.</p>
                <a href="{{ route('spk.alternatif') }}"
                    class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                    Pilih Produk
                </a>

                <div class="mt-10 bg-white rounded-2xl shadow p-6">
                    <h2 class="text-2xl font-bold mb-4">Hasil Perhitungan Metode Weighted Product</h2>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border border-collapse mb-6">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border p-2">#</th>
                                    <th class="border p-2">Produk</th>
                                    <th class="border p-2">Frekuensi</th>
                                    <th class="border p-2">Keuntungan / Unit</th>
                                    <th class="border p-2">Kecepatan</th>
                                    <th class="border p-2">Harga Supplier</th>
                                    <th class="border p-2">Nilai WP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hasil as $i => $item)
                                    <tr>
                                        <td class="border p-2 text-center">{{ $i + 1 }}</td>
                                        <td class="border p-2 font-semibold">{{ $item['nama'] }}</td>
                                        <td class="border p-2 text-center">{{ $item['frekuensi'] }}</td>
                                        <td class="border p-2 text-center">Rp{{ number_format($item['keuntungan']) }}</td>
                                        <td class="border p-2 text-center">{{ $item['kecepatan'] }}</td>
                                        <td class="border p-2 text-center">Rp{{ number_format($item['harga_beli']) }}</td>
                                        <td class="border p-2 text-center text-blue-700 font-bold">
                                            {{ number_format($item['nilai_normalisasi'], 3) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>

@endsection