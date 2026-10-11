<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Pengajuan PKL</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th { background: #4f46e5; color: white; padding: 6px 8px; text-align: left; font-size: 11px; }
        table td { padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 11px; vertical-align: top; }
        table tr:nth-child(even) { background: #f9fafb; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DAFTAR PENGAJUAN PKL</h1>
        @if (!empty($jurusan))
            <p>Kelas / Jurusan: {{ $jurusan }}</p>
        @else
            <p>Kelas / Jurusan: Semua Kelas</p>
        @endif
        <p>Tanggal Cetak: {{ now()->format('d F Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>No Telepon</th>
                <th>Pilihan 1</th>
                <th>Pilihan 2</th>
                <th>Status</th>
                <th>Penempatan</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $index => $p)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $p->siswa->nis ?? '-' }}</td>
                    <td>{{ $p->siswa->nama_siswa ?? '-' }}</td>
                    <td>{{ $p->jurusan ?? '-' }}</td>
                    <td>{{ $p->siswa->no_telepon ?? '-' }}</td>
                    <td>{{ $p->pilihan_1 }}{{ $p->industri_1 ? ' - ' . $p->industri_1 : '' }}</td>
                    <td>{{ $p->pilihan_2 }}{{ $p->industri_2 ? ' - ' . $p->industri_2 : '' }}</td>
                    <td>{{ ucfirst($p->status) }}</td>
                    <td>{{ $p->penempatan_id ? 'Sudah' : 'Belum' }}</td>
                    <td>{{ $p->created_at ? $p->created_at->format('d/m/Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:30px;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak dari Sistem PKL &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
