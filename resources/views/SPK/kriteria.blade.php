@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">Perbandingan Kriteria (AHP)</h2>

        <!-- Success Alert -->
        @if(session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            </script>
        @endif

        <!-- Error Alerts -->
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Panduan Skala -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">
            <h3 class="font-semibold text-blue-800 mb-2">Petunjuk Penilaian:</h3>
            <table class="text-sm w-full border-collapse">
                <tr class="border-b">
                    <td class="py-1 w-20"><b>1</b></td>
                    <td>Kedua kriteria sama penting</td>
                </tr>
                <tr class="border-b">
                    <td><b>2</b></td>
                    <td>Satu kriteria agak lebih penting</td>
                </tr>
                <tr class="border-b">
                    <td><b>3</b></td>
                    <td>Satu kriteria lebih penting</td>
                </tr>
                <tr>
                    <td><b>0.5 / 0.33</b></td>
                    <td>Kebalikan dari 2 atau 3 (jika kriteria B jauh lebih penting)</td>
                </tr>
            </table>
        </div>

        <!-- Form Perbandingan -->
        <form method="POST" action="{{ route('spk.kriteria.proses') }}">
            @csrf

            @php
                $kriteria = \App\Models\SPKKriteria::pluck('nama_kriteria')->toArray();
                $n = count($kriteria);
            @endphp


            <div class="space-y-5">
                @for ($i = 0; $i < $n; $i++)
                    @for ($j = $i + 1; $j < $n; $j++)
                        <div class="p-4 border rounded-md bg-gray-50 hover:bg-gray-100 transition">
                            <label class="block font-semibold mb-2 text-gray-800">
                                Mana yang lebih penting?
                                <span class="text-blue-600">{{ $kriteria[$i] }}</span>
                                atau
                                <span class="text-green-600">{{ $kriteria[$j] }}</span>?
                            </label>

                            <select name="perbandingan[{{ $i }}][{{ $j }}]" required
                                class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200">
                                <option value="">-- Pilih --</option>

                                <!-- Pilihan: kriteria[i] lebih penting -->
                                <option value="1">1 - Sama penting</option>
                                <option value="2">2 - {{ $kriteria[$i] }} agak lebih penting</option>
                                <option value="3">3 - {{ $kriteria[$i] }} lebih penting</option>

                                <!-- Pilihan: kriteria[j] lebih penting (kebalikan) -->
                                <option value="0.5">0.5 - {{ $kriteria[$j] }} agak lebih penting</option>
                                <option value="0.33">0.33 - {{ $kriteria[$j] }} lebih penting</option>
                            </select>
                        </div>
                    @endfor
                @endfor
            </div>

            <div class="mt-6">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-medium transition">
                    Proses AHP
                </button>
            </div>
        </form>
    </div>
@endsection