@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Laporan Penjualan & Laba</h2>
    </div>

    <!-- Filter tanggal -->
    <form method="GET" action="{{ route('laporan.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 items-end">
        <div>
            <label class="text-sm font-medium text-gray-700">Dari:</label>
            <input type="date" name="dari" value="{{ request('dari') }}"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400">
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Sampai:</label>
            <input type="date" name="sampai" value="{{ request('sampai') }}"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400">
        </div>
        <div class="col-span-2 md:col-span-1">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm shadow">
                Filter
            </button>
        </div>
        <div class="col-span-2 md:col-span-1">
            <a href="{{ route('laporan.print', request()->query()) }}" target="_blank"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm shadow">
                Print Laporan
            </a>
        </div>
    </form>


    <!-- Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-green-100 text-green-800 p-4 rounded shadow">
            <h4 class="font-bold">Total Pemasukan</h4>
            <p class="text-lg">Rp {{ number_format($total_pemasukan, 0, ',', '.') }}</p>
        </div>
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded shadow">
            <h4 class="font-bold">Total Pengeluaran</h4>
            <p class="text-lg">Rp {{ number_format($total_pengeluaran, 0, ',', '.') }}</p>
        </div>
        <div class="bg-blue-100 text-blue-800 p-4 rounded shadow">
            <h4 class="font-bold">Pendapatan</h4>
            <p class="text-lg">Rp {{ number_format($pendapatan, 0, ',', '.') }}</p>
        </div>
    </div>


    <!-- Tabel Transaksi -->
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full text-left text-sm border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 border">Tanggal</th>
                    <th class="py-2 px-4 border">Kode Transaksi</th>
                    <th class="py-2 px-4 border">Nama Produk & Jumlah</th>
                    <th class="py-2 px-4 border">Pemasukan</th>
                    <th class="py-2 px-4 border">Pengeluaran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksi as $trx)
                    <tr>
                        <td class="py-2 px-4 border">{{ $trx->created_at->format('d/m/Y') }}</td>
                        <td class="py-2 px-4 border">{{ $trx->kode_transaksi }}</td>
                        <td class="py-2 px-4 border">
                            @foreach($trx->details as $detail)
                                {{ $detail->product->nama ?? '-' }}: {{ $detail->jumlah }} {{ $detail->satuan ?? '' }}<br>
                            @endforeach
                        </td>
                        <td class="py-2 px-4 border">
                            @if($trx->jenis === 'pemasukan')
                                Rp {{ number_format($trx->total_harga, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="py-2 px-4 border">
                            @if($trx->jenis === 'pengeluaran')
                                Rp {{ number_format($trx->total_harga, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">Tidak ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $transaksi->links() }}
        </div>

    </div>
@endsection