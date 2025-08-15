@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Edit Produk</h2>

        @if ($errors->any())
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <ul class="text-sm list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('produk.update', $produk->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="nama" class="block font-medium text-sm mb-1">Nama Produk</label>
                <input type="text" name="nama" id="nama" value="{{ old('nama', $produk->nama) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400"
                    required>
            </div>

            <div class="mb-4">
                <label for="kategori" class="block font-medium text-sm mb-1">Kategori</label>
                <select name="kategori" id="kategori"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400"
                    required>
                    <option value="telur" {{ old('kategori', $produk->kategori) == 'telur' ? 'selected' : '' }}>Telur</option>
                    <option value="beras" {{ old('kategori', $produk->kategori) == 'beras' ? 'selected' : '' }}>Beras</option>
                    <option value="mie" {{ old('kategori', $produk->kategori) == 'mie' ? 'selected' : '' }}>Mie</option>
                    <option value="minuman" {{ old('kategori', $produk->kategori) == 'minuman' ? 'selected' : '' }}>Minuman
                    </option>
                    <option value="gas" {{ old('kategori', $produk->kategori) == 'gas' ? 'selected' : '' }}>Gas</option>
                    <option value="jajanan" {{ old('kategori', $produk->kategori) == 'jajanan' ? 'selected' : '' }}>Jajanan
                    </option>
                    <option value="bahan masak" {{ old('kategori', $produk->kategori) == 'bahan masak' ? 'selected' : '' }}>
                        Bahan masak</option>
                    <option value="sembako" {{ old('kategori', $produk->kategori) == 'sembako' ? 'selected' : '' }}>Sembako
                    </option>
                    <option value="lainnya" {{ old('kategori', $produk->kategori) == 'lainnya' ? 'selected' : '' }}>Lainnya
                    </option>
                </select>
            </div>

            <div class="mb-4">
                <label for="harga_beli" class="block font-medium text-sm mb-1">Harga Beli</label>
                <input type="number" name="harga_beli" id="harga_beli" value="{{ old('harga_beli', $produk->harga_beli) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="harga_jual" class="block font-medium text-sm mb-1">Harga Jual</label>
                <input type="number" name="harga_jual" id="harga_jual" value="{{ old('harga_jual', $produk->harga_jual) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="stok" class="block font-medium text-sm mb-1">Stok</label>
                <input type="number" name="stok" id="stok" value="{{ old('stok', $produk->stok) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('produk.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                    Perbarui
                </button>
            </div>
        </form>
    </div>
@endsection