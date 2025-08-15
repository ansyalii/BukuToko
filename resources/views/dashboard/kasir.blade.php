@extends('layouts.app')

@section('content')
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Kasir</h2>

    <div class="bg-white p-6 rounded shadow mb-6">
        <h3 class="text-gray-700 text-lg">Selamat datang, {{ auth()->user()->name }}!</h3>
        <p class="mt-2 text-gray-600">Silakan mulai mencatat transaksi penjualan atau melihat stok barang.</p>
    </div>

    <!-- Akses cepat -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="#"
            class="block bg-blue-500 hover:bg-blue-600 text-white text-center py-4 rounded shadow text-lg font-medium">+
            Catat Transaksi</a>
        <a href="#"
            class="block bg-gray-700 hover:bg-gray-800 text-white text-center py-4 rounded shadow text-lg font-medium">Lihat
            Stok</a>
    </div>
@endsection