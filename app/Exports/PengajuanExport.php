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
            'Kelas',
            'No Telepon',
            'Pilihan 1',
            'Pilihan 2',
            'Status Pengajuan',
            'Status Penempatan',
            'Tanggal',
        ];
    }

    public function collection(): Collection
    {
        return $this->pengajuans->values()->map(function ($p, $i) {
            return [
                $i + 1,
                $p->siswa->nis ?? '-',
                $p->siswa->nama_siswa ?? '-',
                $p->jurusan ?? '-',
                $p->siswa->no_telepon ?? '-',
                trim(($p->pilihan_1 ?? '') . ($p->industri_1 ? ' - ' . $p->industri_1 : '')),
                trim(($p->pilihan_2 ?? '') . ($p->industri_2 ? ' - ' . $p->industri_2 : '')),
                ucfirst($p->status ?? '-'),
                $p->penempatan_id ? 'Sudah' : 'Belum',
                $p->created_at ? $p->created_at->format('d/m/Y') : '-',
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
