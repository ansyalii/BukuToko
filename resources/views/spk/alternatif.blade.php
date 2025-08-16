@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto bg-white p-8 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Pilih Produk Alternatif</h2>

        @if(session('success'))
            <div class="mb-4 p-4 rounded bg-green-100 border border-green-300 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('spk.alternatif.simpan') }}" method="POST">
            @csrf

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($produk as $product)
                    <label
                        class="flex items-center gap-4 p-4 rounded-lg border border-gray-300 bg-gray-50 hover:bg-gray-100 transition cursor-pointer">
                        <input type="checkbox" name="produk_id[]" value="{{ $product->id }}" {{ in_array($product->id, $terpilih) ? 'checked' : '' }} class="h-5 w-5 text-blue-600 rounded">
                        <div>
                            <p class="font-medium text-gray-800">{{ $product->nama }}</p>
                            <p class="text-sm text-gray-600">Stok: {{ $product->stok }}</p>
                        </div>
                    </label>
                @empty
                    <p class="text-gray-600 col-span-full">Belum ada produk yang tersedia.</p>
                @endforelse
            </div>

            <div class="mt-6">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded transition">
                    Simpan Alternatif
                </button>
            </div>
        </form>

        @if(count($terpilih) > 0)
            <div class="mt-10 border-t pt-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Produk Alternatif Terpilih:</h3>
                <ul class="list-disc pl-6 space-y-1 text-gray-700">
                    @foreach ($produk->whereIn('id', $terpilih) as $p)
                        <li>{{ $p->nama }} (Stok: {{ $p->stok }})</li>
                    @endforeach
                </ul>

                <div class="mt-6">
                    <a href="{{ route('spk.wp') }}"
                        class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded transition">
                        Lanjut ke Penilaian WP
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection