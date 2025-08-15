@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Tambah Produk</h2>

        @if ($errors->any())
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <ul class="text-sm list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('produk.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="nama" class="block font-medium text-sm mb-1">Nama Produk</label>
                <input type="text" name="nama" id="nama" value="{{ old('nama') }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400"
                    required>
            </div>

            <div class="mb-4">
                <label for="kategori" class="block font-medium text-sm mb-1">Kategori</label>
                <select name="kategori" id="kategori"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400">
                    <option value="">-- Pilih --</option>
                    <option value="telur">Telur</option>
                    <option value="beras">Beras</option>
                    <option value="mie">Mie</option>
                    <option value="minuman">Minuman</option>
                    <option value="gas">Gas</option>
                    <option value="jajanan">Jajanan</option>
                    <option value="bahan masak">Bahan masak</option>
                    <option value="sembako">Sembako</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="satuan" class="block text-sm font-medium text-gray-700">Satuan</label>
                <input type="text" name="satuan" id="satuan" value="{{ old('satuan') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required>
                <small class="text-gray-500">Contoh: kg, liter, pcs, butir</small>
            </div>

            <div class="mb-4">
                <label for="harga_beli" class="block font-medium text-sm mb-1">Harga Beli</label>
                <input type="number" name="harga_beli" id="harga_beli" value="{{ old('harga_beli') }}"
                    class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="harga_jual" class="block font-medium text-sm mb-1">Harga Jual</label>
                <input type="number" name="harga_jual" id="harga_jual" value="{{ old('harga_jual') }}"
                    class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="stok" class="block font-medium text-sm mb-1">Stok</label>
                <input type="number" name="stok" id="stok" value="{{ old('stok') }}"
                    class="w-full border border-gray-300 rounded px-3 py-2" required>
                <p class="text-xs text-gray-500 mt-1">Masukkan jumlah stok sesuai satuan yang digunakan</p>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('produk.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                    Simpan
                </button>
            </div>
        </form>
    </div>
@endsection