<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetailPemesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ganti sesuai kebutuhan auth
    }

    public function rules(): array
    {
        return [
            'total_item' => 'required|integer',
            'total_harga' => 'required|integer',
            'bayar' => 'required|integer',
            'metode' => 'required|in:Cash,Qris',
        ];
    }
}
