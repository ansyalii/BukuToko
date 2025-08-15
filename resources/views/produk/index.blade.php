@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Produk</h2>
        <a href="{{ route('produk.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow text-sm">
            + Tambah Produk
        </a>
    </div>

    <!-- Form Search -->
    <form method="GET" action="{{ route('produk.index') }}" class="mb-4">
        <div class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kategori..."
                class="w-full sm:w-64 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400">

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                Cari
            </button>
        </div>
    </form>

    <!-- Alert -->
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session("success") }}',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif

    <!-- Tabel Produk -->
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="py-3 px-4">No</th>
                    <th class="py-3 px-4">Nama</th>
                    <th class="py-3 px-4">Kategori</th>
                    <th class="py-3 px-4">Harga Beli</th>
                    <th class="py-3 px-4">Harga Jual</th>
                    <th class="py-3 px-4">Stok</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($produk as $index => $item)
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ $index + $produk->firstItem() }}</td>
                        <td class="py-2 px-4">{{ $item->nama }}</td>
                        <td class="py-2 px-4">{{ $item->kategori ?? '-' }}</td>
                        <td class="py-2 px-4">Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                        <td class="py-2 px-4">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                        <td class="py-2 px-4">
                            @php
                                $kategori = strtolower($item->kategori);
                                $isKg = in_array($kategori, ['telur', 'beras']);
                                $satuan = $isKg ? 'kg' : 'pcs';
                                $jumlah = $item->stok;
                            @endphp

                            {{ $jumlah }} {{ $satuan }}

                            @if ($kategori === 'telur' && $item->konversi_kg)
                                @php
                                    $butir = $item->stok * $item->konversi_kg;
                                @endphp
                                <span class="text-xs text-gray-500">(± {{ $butir }} butir)</span>
                            @endif
                        </td>

                        <td class="py-2 px-4 text-center">
                            <a href="{{ route('produk.edit', $item->id) }}"
                                class="bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded text-xs">Edit</a>

                            <form action="{{ route('produk.destroy', $item->id) }}" method="POST" class="inline"
                                onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-4 px-4 text-center text-gray-500">Belum ada produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $produk->links() }}
    </div>
@endsection