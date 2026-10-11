<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PengajuanExport;
use App\Http\Controllers\Controller;
use App\Models\PengajuanPkl;
use App\Models\Kompetensi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $pengajuans = $this->filteredQuery($request)->paginate(10)->withQueryString();
        $jurusanList = $this->jurusanList();

        return view('admin.pengajuan.index', compact('pengajuans', 'jurusanList'));
    }

    public function exportPdf(Request $request)
    {
        $pengajuans = $this->filteredQuery($request)->get();
        $jurusan = $request->jurusan;

        $pdf = Pdf::loadView('admin.pengajuan.export_pdf', compact('pengajuans', 'jurusan'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('pengajuan-pkl-' . now()->format('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $pengajuans = $this->filteredQuery($request)->get();

        return Excel::download(
            new PengajuanExport($pengajuans),
            'pengajuan-pkl-' . now()->format('Ymd') . '.xlsx'
        );
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
        $kompetensis = Kompetensi::all();
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

    /**
     * Query pengajuan dengan filter kelas (jurusan).
     */
    private function filteredQuery(Request $request)
    {
        $query = PengajuanPkl::with(['siswa', 'penempatan']);

        if ($request->filled('jurusan')) {
            $query->where('jurusan', $request->jurusan);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Daftar kelas/jurusan yang tersedia untuk filter.
     */
    private function jurusanList()
    {
        return PengajuanPkl::query()
            ->whereNotNull('jurusan')
            ->where('jurusan', '!=', '')
            ->distinct()
            ->orderBy('jurusan')
            ->pluck('jurusan');
    }
}
