<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PengajuanExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected Collection $pengajuans;

    public function __construct(Collection $pengajuans)
    {
        $this->pengajuans = $pengajuans;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'Nama Siswa',
            'Jurusan',
            'No Telepon',
            'Pilihan 1',
            'Industri Pilihan 1',
            'Pilihan 2',
            'Industri Pilihan 2',
            'Pekerjaan Orang Tua',
            'Penghasilan Orang Tua',
            'Alamat Rumah',
            'Status Pengajuan',
            'Tempat Diterima',
            'Catatan Admin',
            'Tanggal Pengajuan',
            // Data Penempatan (jika sudah ada)
            'Industri Penempatan',
            'Guru Pembimbing',
            'Kompetensi',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Status Penempatan',
        ];
    }

    public function collection(): Collection
    {
        return $this->pengajuans->values()->map(function ($p, $i) {
            // Cek jika pengajuan sudah memiliki penempatan
            $penempatan = $p->penempatan;
            
            return [
                $i + 1,
                $p->siswa->nis ?? '-',
                $p->siswa->nama_siswa ?? '-',
                $p->jurusan ?? '-',
                $p->siswa->no_telepon ?? '-',
                $p->pilihan_1 ?? '-',
                $p->industri_1 ?? '-',
                $p->pilihan_2 ?? '-',
                $p->industri_2 ?? '-',
                $p->pekerjaan_orang_tua ?? '-',
                // Format penghasilan orang tua
                $p->penghasilan_ortu ? 'Rp ' . number_format((int) str_replace('.', '', $p->penghasilan_ortu), 0, ',', '.') : '-',
                $p->alamat ?? '-',
                // Status Pengajuan (Pending/Diterima/Ditolak)
                ucfirst($p->status ?? '-'),
                $p->tempat_diterima ?? '-',
                $p->catatan_admin ?? '-',
                $p->created_at ? $p->created_at->format('d/m/Y H:i') : '-',
                
                // --- DATA PENEMPATAN ---
                $penempatan->industri->nama_perusahaan ?? '-',
                $penempatan->guru->nama_guru ?? '-',
                $penempatan->kompetensi->nama_kompetensi ?? '-',
                $penempatan && $penempatan->tanggal_mulai ? \Carbon\Carbon::parse($penempatan->tanggal_mulai)->format('d/m/Y') : '-',
                $penempatan && $penempatan->tanggal_selesai ? \Carbon\Carbon::parse($penempatan->tanggal_selesai)->format('d/m/Y') : '-',
                $penempatan ? ucfirst($penempatan->status) : 'Belum Ditempatkan',
            ];
        });
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]], // Membuat baris header menjadi tebal
        ];
    }
}