<?php

namespace App\Http\Controllers\Industri;

use App\Http\Controllers\Controller;
use App\Models\MonitoringNilai;
use App\Models\PenempatanPkl;
use App\Models\TemplatePenilaian;
use Illuminate\Http\Request;

class PenilaianController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $industri = $user->industri;

        if (!$industri) {
            return redirect()->back()->with('error', 'Data industri tidak ditemukan.');
        }

        $query = PenempatanPkl::with(['siswa', 'guru', 'kompetensi'])
            ->where('industri_id', $industri->id)
            ->where('status', 'aktif');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $penempatans = $query->paginate(10);

        return view('industri.penilaian.index', compact('penempatans', 'industri'));
    }

    public function show($penempatan_id)
    {
        $user = auth()->user();
        $industri = $user->industri;

        $penempatan = PenempatanPkl::with(['siswa', 'guru', 'kompetensi', 'industri'])
            ->where('industri_id', $industri->id)
            ->findOrFail($penempatan_id);

        $siswaJurusan = $penempatan->siswa->jurusan ?? null;

        $templates = TemplatePenilaian::active()
            ->forJurusan($siswaJurusan)
            ->with('children')
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->get();

        $kejuruanRoot = $templates->where('kategori', 'kejuruan')->whereNull('parent_id')->sortBy('urutan');
        $sikapItems = $templates->where('kategori', 'sikap')->sortBy('urutan');

        $existingNilais = MonitoringNilai::where('penempatan_id', $penempatan->id)
            ->where('role_penilai', 'industri')
            ->get()
            ->keyBy('aspek_penilaian');

        return view('industri.penilaian.show', compact(
            'penempatan', 'templates', 'existingNilais', 'kejuruanRoot', 'sikapItems'
        ));
    }

    public function store(Request $request, $penempatan_id)
    {
        $penempatan = PenempatanPkl::findOrFail($penempatan_id);

        // Validasi akses (pastikan industri ini memang membimbing siswa tersebut)
        if ($penempatan->industri_id != auth()->user()->industri->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        $siswaJurusan = $penempatan->siswa->jurusan ?? null;

        $templates = TemplatePenilaian::active()
            ->where('tipe', 'item')
            ->forJurusan($siswaJurusan)
            ->get();

        // Gabungan: fitur "kosongkan nilai" (hapus) dari Siti + transaksi &
        // validasi dari perbaikan keamanan. Semua perubahan atomic.
        $saved = 0;
        $deleted = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($templates, $request, $penempatan, &$saved, &$deleted) {
            foreach ($templates as $template) {
                $nilaiKey = 'nilai_' . $template->id;
                $nilaiVal = $request->input($nilaiKey);

                // Nilai dikosongkan (null / string kosong) -> hapus nilai lama
                if ($nilaiVal === null || $nilaiVal === '') {
                    $deletedCount = MonitoringNilai::where('penempatan_id', $penempatan->id)
                        ->where('aspek_penilaian', $template->nama_aspek)
                        ->where('role_penilai', 'industri')
                        ->delete();

                    if ($deletedCount > 0) {
                        $deleted++;
                    }

                    continue;
                }

                $request->validate([
                    $nilaiKey => 'required|integer|min:0|max:100',
                ]);

                $catatanKey = 'catatan_' . $template->id;
                $catatan = $request->input($catatanKey, null);
                if (is_array($catatan)) {
                    $catatan = null;
                }

                MonitoringNilai::updateOrCreate(
                    [
                        'penempatan_id' => $penempatan->id,
                        'aspek_penilaian' => $template->nama_aspek,
                        'role_penilai' => 'industri',
                    ],
                    [
                        // monitoring_nilai hanya punya kolom guru_id (tidak ada
                        // industri_id), jadi simpan guru pembimbing penempatan.
                        'guru_id' => $penempatan->guru_id,
                        'nilai' => (int) $nilaiVal,
                        'catatan' => $catatan,
                        'is_hidden_from_siswa' => false,
                        'tanggal_penilaian' => now()->toDateString(),
                    ]
                );
                $saved++;
            }
        });

        // Pesan feedback (gaya Siti)
        if ($saved > 0 || $deleted > 0) {
            return redirect()->route('industri.penilaian.show', $penempatan->id)
                ->with('success', "Berhasil memproses penilaian. Disimpan: {$saved}, Dikosongkan: {$deleted}.");
        }

        return redirect()->route('industri.penilaian.show', $penempatan->id)
            ->with('info', 'Tidak ada perubahan nilai.');
    }
}
