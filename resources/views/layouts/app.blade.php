<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Buku Besar Digital') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">

    <!-- Navbar -->
    <nav class="bg-white shadow-md p-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <!-- Toggle Sidebar Button -->
            <button @click="sidebarOpen = !sidebarOpen" class="md:hidden block focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <h1 class="text-xl font-bold text-blue-600">BukuToko.id</h1>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-gray-600 text-sm hidden sm:block">
                Halo, {{ Auth::user()->nama_user }}
            </span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">Logout</button>
            </form>
        </div>
    </nav>

    <!-- Wrapper -->
    <div class="relative min-h-screen md:flex">

        <!-- Sidebar -->
        <aside
            class="fixed z-30 inset-y-0 left-0 w-64 bg-white shadow-md transform transition-transform duration-300 ease-in-out md:relative md:translate-x-0 md:z-auto h-screen overflow-y-auto"
            :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">

            <div class="p-6">
                <ul class="space-y-4 text-gray-700">
                    <li><a href="{{ route('dashboard.index') }}" class="hover:text-blue-500">Dashboard</a></li>
                    <li><a href="{{ route('produk.index') }}" class="hover:text-blue-500">Manajemen Produk</a></li>
                    <li><a href="{{ route('laporan.index') }}" class="hover:text-blue-500">Laporan Keuangan</a></li>
                    <li><a href="{{ route('spk.index') }}" class="hover:text-blue-500">SPK Pembelian</a></li>
                    <li><a href="{{ route('laporan.transaksi') }}" class="hover:text-blue-500">Pencatatan</a></li>
                </ul>
            </div>
        </aside>

        <!-- Overlay (for mobile) -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black opacity-50 z-20 md:hidden"
            x-transition x-cloak></div>

        <!-- Main Content -->
        <main class="flex-1 p-6 z-10">
            @yield('content')
        </main>

    </div>

</body>

</html>