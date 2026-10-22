<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\MonitoringNilai;
use App\Models\PenempatanPkl;
use App\Models\TemplatePenilaian;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    public function index(Request $request)
    {
        $guru = auth()->user()->guru;

        $query = PenempatanPkl::with(['siswa', 'industri'])
            ->where('guru_id', $guru->id);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $penempatans = $query->paginate(10);
        return view('guru.nilai.index', compact('penempatans'));
    }

    public function create(Request $request)
    {
        $penempatan_id = $request->query('penempatan_id');

        if (!$penempatan_id) {
            return redirect()->route('guru.nilai.index')
                ->with('error', 'Penempatan tidak ditemukan.');
        }

        $penempatan = PenempatanPkl::with(['siswa', 'kompetensi', 'industri'])
            ->findOrFail($penempatan_id);

        if ($penempatan->guru_id != auth()->user()->guru->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        $siswaJurusan = $penempatan->siswa->jurusan ?? null;
        $templates = TemplatePenilaian::active()->forJurusan($siswaJurusan)->with('children')->orderBy('kategori')->orderBy('urutan')->get();
        $kejuruanRoot = $templates->where('kategori', 'kejuruan')->whereNull('parent_id')->sortBy('urutan');
        $sikapItems = $templates->where('kategori', 'sikap')->sortBy('urutan');

        $existingNilais = MonitoringNilai::where('penempatan_id', $penempatan->id)
            ->where('role_penilai', 'guru')
            ->get()
            ->keyBy('aspek_penilaian');

        return view('guru.nilai.create', compact('penempatan', 'templates', 'existingNilais', 'kejuruanRoot', 'sikapItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'penempatan_id' => 'required|exists:penempatan_pkl,id',
        ]);

        $penempatan = PenempatanPkl::with('siswa')->findOrFail($request->penempatan_id);

        if ($penempatan->guru_id != auth()->user()->guru->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        $siswaJurusan = $penempatan->siswa->jurusan ?? null;
        $templates = TemplatePenilaian::active()->where('tipe', 'item')->forJurusan($siswaJurusan)->get();

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
                        ->where('role_penilai', 'guru')
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
                        'role_penilai' => 'guru',
                    ],
                    [
                        'guru_id' => auth()->user()->guru->id,
                        'nilai' => (int) $nilaiVal,
                        'catatan' => $catatan,
                        'is_hidden_from_siswa' => false,
                        'tanggal_penilaian' => now()->toDateString(),
                    ]
                );
                $saved++;
            }
        });

        // Pesan sukses berdasarkan aksi yang dilakukan
        if ($saved > 0 && $deleted > 0) {
            return redirect()->route('guru.nilai.show', $penempatan->id)
                ->with('success', "Berhasil menyimpan {$saved} nilai dan menghapus {$deleted} nilai kosong.");
        } elseif ($saved > 0) {
            return redirect()->route('guru.nilai.show', $penempatan->id)
                ->with('success', "Berhasil menyimpan {$saved} penilaian.");
        } elseif ($deleted > 0) {
            return redirect()->route('guru.nilai.show', $penempatan->id)
                ->with('success', "Berhasil mengosongkan {$deleted} penilaian.");
        }

        return redirect()->route('guru.nilai.show', $penempatan->id)
            ->with('info', 'Tidak ada perubahan pada nilai.');
    }

    public function show($penempatan_id)
    {
        $penempatan = PenempatanPkl::with(['siswa', 'kompetensi', 'industri'])
            ->findOrFail($penempatan_id);

        if ($penempatan->guru_id != auth()->user()->guru->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        $siswaJurusan = $penempatan->siswa->jurusan ?? null;
        $templates = TemplatePenilaian::active()->forJurusan($siswaJurusan)->with('children')->orderBy('kategori')->orderBy('urutan')->get();
        $kejuruanRoot = $templates->where('kategori', 'kejuruan')->whereNull('parent_id')->sortBy('urutan');
        $sikapItems = $templates->where('kategori', 'sikap')->sortBy('urutan');

        $nilais = MonitoringNilai::where('penempatan_id', $penempatan_id)
            ->where('role_penilai', 'guru')
            ->get()
            ->keyBy('aspek_penilaian');

        return view('guru.nilai.show', compact('penempatan', 'nilais', 'templates', 'kejuruanRoot', 'sikapItems'));
    }

    public function edit($penempatan_id)
    {
        $penempatan = PenempatanPkl::with(['siswa', 'kompetensi', 'industri'])
            ->findOrFail($penempatan_id);

        if ($penempatan->guru_id != auth()->user()->guru->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        return redirect()->route('guru.nilai.create', ['penempatan_id' => $penempatan->id]);
    }

    public function destroy($id)
    {
        $nilai = MonitoringNilai::findOrFail($id);

        if ($nilai->guru_id != auth()->user()->guru->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        $nilai->delete();

        return redirect()->back()->with('success', 'Nilai berhasil dihapus.');
    }
}
