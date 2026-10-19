<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JurnalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role === 'siswa' || auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        // penempatan_id & status TIDAK lagi diterima dari input user:
        // penempatan selalu diambil dari akun siswa yang login (mencegah
        // siswa menulis jurnal ke penempatan siswa lain / IDOR),
        // dan status selalu 'draft' (pengiriman hanya lewat tombol Submit).
        return [
            'tanggal' => 'required|date',
            'aktivitas' => 'required|string',
            'dokumentasi' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'aktivitas.required' => 'Aktivitas wajib diisi.',
            'dokumentasi.image' => 'File harus berupa gambar.',
            'dokumentasi.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif.',
            'dokumentasi.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
