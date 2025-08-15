@extends('layouts.app')

@section('content')
    <h2 class="text-4xl font-bold text-blue-600 mb-6">BukuToko.id</h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-4 rounded shadow">
            <h3 class="text-gray-600">Total Transaksi</h3>
            <p class="text-2xl font-bold text-blue-600">{{ $totalTransaksi }}</p>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <h3 class="text-gray-600">Total Produk</h3>
            <p class="text-2xl font-bold text-green-600">{{ $totalProduk }}</p>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <h3 class="text-gray-600">Pendapatan Bulan Ini</h3>
            <p class="text-2xl font-bold text-yellow-600">Rp {{ number_format($pendapatan_bersih, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <h3 class="text-gray-600">Produk Hampir Habis</h3>
            <p class="text-2xl font-bold text-red-500">{{ $produkMenipis->count() }} Produk</p>
        </div>
    </div>

    {{-- Chart Slider --}}
    <div class="bg-white p-6 rounded shadow mt-6">
        <div class="swiper mySwiper" style="height: 320px;">
            <div class="swiper-wrapper">

                {{-- Slide 1: Frekuensi Penjualan --}}
                <div class="swiper-slide flex flex-col">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">5 Produk Terlaris Minggu Ini</h3>
                    <div class="flex-1">
                        <canvas id="chartFrekuensi"></canvas>
                    </div>
                </div>

                {{-- Slide 2: Kecepatan Barang Habis --}}
                <div class="swiper-slide flex flex-col">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Kecepatan Barang Habis Minggu Ini</h3>
                    <div class="flex-1">
                        <canvas id="chartKecepatan"></canvas>
                    </div>
                </div>

            </div>
            <div class="swiper-pagination mt-2"></div>
        </div>
    </div>

    {{-- Tabel Produk Hampir Habis --}}
    <div class="bg-white p-4 rounded shadow mt-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Produk dengan Stok Menipis</h3>
        <table class="w-full text-sm text-left text-gray-600">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4">Nama Produk</th>
                    <th class="py-2 px-4">Stok</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($produkMenipis as $produk)
                    <tr>
                        <td class="py-2 px-4">{{ $produk->nama }}</td>
                        <td class="py-2 px-4">{{ $produk->stok }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="py-2 px-4 text-center">Tidak ada produk yang menipis</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Script --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Swiper init
        new Swiper(".mySwiper", {
            pagination: { el: ".swiper-pagination", clickable: true }
        });

        // Chart Frekuensi
        new Chart(document.getElementById('chartFrekuensi').getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($produkChartFrekuensi->pluck('nama')) !!},
                datasets: [{
                    label: 'Frekuensi Penjualan Minggu Ini',
                    data: {!! json_encode($produkChartFrekuensi->pluck('frekuensi')) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });

        // Chart Kecepatan Barang Habis
        new Chart(document.getElementById('chartKecepatan').getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($produkChartKecepatan->pluck('nama')) !!},
                datasets: [{
                    label: 'Kecepatan Habis Minggu Ini',
                    data: {!! json_encode($produkChartKecepatan->pluck('kecepatan')) !!},
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    </script>
@endsection