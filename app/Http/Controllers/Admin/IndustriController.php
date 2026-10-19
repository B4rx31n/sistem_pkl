<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndustriRequest;
use App\Models\Industri;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class IndustriController extends Controller
{
    public function index(Request $request)
    {
        $query = Industri::with('user');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_perusahaan', 'like', "%{$search}%")
                    ->orWhere('nama_perusahaan', 'like', "%{$search}%")
                    ->orWhere('bidang_usaha', 'like', "%{$search}%")
                    ->orWhere('penanggung_jawab', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter Status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter Kategori Jurusan
        if ($request->has('jurusan') && $request->jurusan != '') {
            $query->where('kategori', $request->jurusan);
        }

        $industris = $query->orderBy('nama_perusahaan')->get();

        $grupIndustri = $industris
            ->groupBy(fn ($i) => $i->kategori ?: 'Semua');

        return view('admin.industri.index', compact('industris', 'grupIndustri'));
    }

    public function create()
    {
        return view('admin.industri.create');
    }

    public function store(IndustriRequest $request)
    {
        // Tentukan lokasi: jika "Lainnya", pakai nama kota yang diketik admin
        $lokasi = $request->lokasi === 'Lainnya' ? $request->lokasi_lainnya : $request->lokasi;

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $lokasi) {
            // Create User
            $user = User::create([
                'name' => $request->nama_perusahaan,
                'email' => $request->email_login,
                'password' => Hash::make($request->password),
            ]);
            $user->forceFill(['role' => 'industri'])->save();

            // Create Industri
            Industri::create([
                'user_id' => $user->id,
                'kode_perusahaan' => $request->kode_perusahaan,
                'nama_perusahaan' => $request->nama_perusahaan,
                'lokasi' => $lokasi,
                'alamat' => $request->alamat,
                'no_telepon' => $request->no_telepon,
                'email' => $request->email,
                'bidang_usaha' => $request->bidang_usaha,
                'kategori' => $request->kategori,
                'jurusan' => $request->kategori,
                'penanggung_jawab' => $request->penanggung_jawab,
                'kuota' => $request->kuota,
                'status' => $request->status,
            ]);
        });

        return redirect()->route('admin.industri.index')
            ->with('success', 'Data industri berhasil ditambahkan.');
    }

    public function show(Industri $industri)
    {
        $industri->load('user');
        return view('admin.industri.show', compact('industri'));
    }

    public function edit(Industri $industri)
    {
        return view('admin.industri.edit', compact('industri'));
    }

    public function update(IndustriRequest $request, Industri $industri)
    {
        $data = $request->validated();

        // Tentukan lokasi: jika "Lainnya", pakai nama kota yang diketik admin
        $lokasi = $data['lokasi'] === 'Lainnya' ? ($data['lokasi_lainnya'] ?? null) : $data['lokasi'];

        \Illuminate\Support\Facades\DB::transaction(function () use ($industri, $data, $lokasi) {
            // Update User (email login & password) - hanya jika industri punya akun login.
            // Industri kustom ("Lainnya") dari form penempatan tidak memiliki akun.
            if ($industri->user) {
                $userData = [
                    'name' => $data['nama_perusahaan'],
                    'email' => $data['email_login'],
                ];

                if (!empty($data['password'])) {
                    $userData['password'] = Hash::make($data['password']);
                }

                $industri->user()->update($userData);
            }

            // Update Industri
            $industri->update([
                'kode_perusahaan' => $data['kode_perusahaan'],
                'nama_perusahaan' => $data['nama_perusahaan'],
                'lokasi' => $lokasi,
                'alamat' => $data['alamat'],
                'no_telepon' => $data['no_telepon'],
                'email' => $data['email'] ?? null,
                'bidang_usaha' => $data['bidang_usaha'],
                'kategori' => $data['kategori'],
                'jurusan' => $data['kategori'],
                'penanggung_jawab' => $data['penanggung_jawab'],
                'kuota' => $data['kuota'],
                'status' => $data['status'],
            ]);
        });

        return redirect()->route('admin.industri.index')
            ->with('success', 'Data industri berhasil diupdate.');
    }

    public function destroy(Industri $industri)
    {
        if ($industri->penempatan()->exists()) {
            return back()->with('error', 'Industri tidak bisa dihapus karena masih memiliki penempatan PKL. Hapus penempatan terlebih dahulu.');
        }

        // Industri kustom ("Lainnya") tidak punya akun login user.
        if ($industri->user) {
            $industri->user->delete();
        }

        $industri->delete();

        return redirect()->route('admin.industri.index')
            ->with('success', 'Data industri berhasil dihapus.');
    }
}
