<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|array',
            'product_id.*' => 'required|exists:product,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|numeric|min:1',
            'total_harga' => 'required|array',
            'total_harga.*' => 'required|numeric|min:1',
            'jenis' => 'required|in:masuk,keluar',
            'tanggal' => 'required|date', // ✅ tanggal validasi ditambahkan di sini
        ];
    }
}