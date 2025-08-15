@extends('layouts.app')

@section('content')
    <style>
        @media print {

            header,
            nav,
            .sidebar,
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                font-size: 12pt;
            }

            /* Hapus padding/margin berlebihan saat print */
            .p-6 {
                padding: 0 !important;
            }

            .text-center {
                margin: 0 !important;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
                /* Supaya header tabel muncul di setiap halaman */
            }

            tfoot {
                display: table-footer-group;
            }
        }
    </style>

    <div class="p-6">
        <div class="text-center mb-2">
            <h1 class="text-2xl font-bold">Laporan Transaksi</h1>
            <p class="text-gray-700 text-sm">
                Periode: {{ $dari ?? 'Semua' }} - {{ $sampai ?? 'Semua' }}
            </p>
        </div>

        <table class="min-w-full border border-gray-300 text-sm">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border py-2 px-2">Tanggal</th>
                    <th class="border py-2 px-2">Kode Transaksi</th>
                    <th class="border py-2 px-2">Nama Produk & Jumlah</th>
                    <th class="border py-2 px-2">Pemasukan</th>
                    <th class="border py-2 px-2">Pengeluaran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksi as $trx)
                    <tr class="even:bg-gray-50">
                        <td class="border py-1 px-2">{{ $trx->created_at->format('d/m/Y') }}</td>
                        <td class="border py-1 px-2">{{ $trx->kode_transaksi }}</td>
                        <td class="border py-1 px-2">
                            @foreach($trx->details as $detail)
                                {{ $detail->product->nama ?? '-' }}: {{ $detail->jumlah }} {{ $detail->satuan ?? '' }}<br>
                            @endforeach
                        </td>
                        <td class="border py-1 px-2 text-right">
                            @if($trx->jenis === 'pemasukan')
                                Rp {{ number_format($trx->total_harga, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="border py-1 px-2 text-right">
                            @if($trx->jenis === 'pengeluaran')
                                Rp {{ number_format($trx->total_harga, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-2 text-gray-500">Tidak ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-100 font-semibold">
                <tr>
                    <td colspan="3" class="border py-1 px-2 text-right">Total</td>
                    <td class="border py-1 px-2 text-right">
                        Rp {{ number_format($transaksi->where('jenis', 'pemasukan')->sum('total_harga'), 0, ',', '.') }}
                    </td>
                    <td class="border py-1 px-2 text-right">
                        Rp {{ number_format($transaksi->where('jenis', 'pengeluaran')->sum('total_harga'), 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="bg-gray-200">
                    <td colspan="3" class="border py-1 px-2 text-right">Pendapatan Bersih</td>
                    <td colspan="2" class="border py-1 px-2 text-right">
                        Rp
                        {{ number_format($transaksi->where('jenis', 'pemasukan')->sum('total_harga') - $transaksi->where('jenis', 'pengeluaran')->sum('total_harga'), 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <script>
        window.onload = function () {
            window.print();
        }
    </script>
@endsection