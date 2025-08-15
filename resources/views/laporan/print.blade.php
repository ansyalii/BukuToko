@extends('layouts.app')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Laporan Transaksi</h2>
    <p>Periode: {{ $dari ?? 'Semua' }} - {{ $sampai ?? 'Semua' }}</p>

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

    <script>
        window.onload = function () {
            window.print();
        }
    </script>
@endsection