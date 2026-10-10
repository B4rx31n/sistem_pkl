<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanPkl;
use App\Models\Industri;
use App\Models\Guru;
use App\Models\Kompetensi;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    public function index()
    {
        $pengajuans = PengajuanPkl::with('siswa')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.pengajuan.index', compact('pengajuans'));
    }

    public function show($id)
    {
        $pengajuan = PengajuanPkl::with('siswa', 'penempatan')->findOrFail($id);

        // Ambil kategori jurusan siswa
        $kategori = $pengajuan->siswa->kategori_jurusan;

        // Filter industri berdasarkan kategori jurusan siswa
        $industriQuery = \App\Models\Industri::where('status', 'aktif')
            ->where(function ($q) use ($kategori) {
                $q->where('kategori', $kategori)
                    ->orWhere('kategori', 'Semua');
            });

        $industris = $industriQuery
            ->with(['penempatan' => function ($q) {
                $q->where('status', 'aktif');
            }])
            ->get();

        $gurus = \App\Models\Guru::all();
        $kompetensis = \App\Models\Kompetensi::where(function ($q) use ($kategori) {
            $q->where('kategori', $kategori)->orWhere('kategori', 'Semua');
        })->get();

        return view('admin.pengajuan.show', compact('pengajuan', 'industris', 'gurus', 'kompetensis'));
    }

    public function update(Request $request, int $id)
    {
        $pengajuan = PengajuanPkl::findOrFail($id);

        // Validasi dasar
        $request->validate([
            'status' => 'required|in:pending,diterima,ditolak',
            'catatan_admin' => 'nullable|string',
        ]);

        // CEK: Jika status diubah dari 'diterima' ke 'pending' atau 'ditolak'
        if ($pengajuan->status == 'diterima' && in_array($request->status, ['pending', 'ditolak'])) {
            if ($pengajuan->penempatan_id) {
                return back()->with('error', 'Tidak dapat mengubah status karena sudah memiliki penempatan. Hapus penempatan terlebih dahulu.');
            }
        }

        // Update data.
        // Catatan: tempat_diterima TIDAK diisi di sini. Nilainya diisi otomatis
        // dari wilayah (lokasi) industri ketika penempatan dibuat oleh admin.
        $pengajuan->status = $request->status;
        $pengajuan->catatan_admin = $request->catatan_admin;
        $pengajuan->save();

        return redirect()->route('admin.pengajuan.show', $pengajuan->id)
            ->with('success', 'Status pengajuan berhasil diperbarui.');
    }
}
