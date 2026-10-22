<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Pengajuan PKL</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 8mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #222;
            margin: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            letter-spacing: 1px;
        }
        .header p {
            margin: 2px 0;
            color: #555;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table th {
            background: #4f46e5;
            color: #fff;
            padding: 4px 3px;
            text-align: center;
            font-size: 8px;
            border: 1px solid #3730a3;
            word-wrap: break-word;
        }
        table td {
            padding: 4px 3px;
            border: 1px solid #d1d5db;
            font-size: 8px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table tr:nth-child(even) td {
            background: #f9fafb;
        }
        .col-no     { width: 3%;  text-align: center; }
        .col-nis    { width: 6%;  text-align: center; }
        .col-nama   { width: 10%; }
        .col-jurusan{ width: 7%; }
        .col-telp   { width: 7%; }
        .col-ortu   { width: 8%; }
        .col-penghasilan { width: 7%; text-align: right; }
        .col-alamat { width: 11%; }
        .col-pil1   { width: 9%; }
        .col-pil2   { width: 9%; }
        .col-status { width: 5%; text-align: center; }
        .col-tempat { width: 8%; }
        .col-penempatan { width: 10%; }
        .col-tanggal{ width: 7%; text-align: center; }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            color: #fff;
        }
        .badge-pending  { background: #f59e0b; }
        .badge-diterima { background: #10b981; }
        .badge-ditolak  { background: #ef4444; }
        .badge-aktif    { background: #10b981; }
        .badge-selesai  { background: #3b82f6; }
        .badge-batal    { background: #ef4444; }
        .badge-belum    { background: #6b7280; }

        .footer {
            text-align: center;
            margin-top: 10px;
            color: #666;
            font-size: 9px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .empty-row td {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #888;
        }
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
                <th class="col-no">No</th>
                <th class="col-nis">NIS</th>
                <th class="col-nama">Nama Siswa</th>
                <th class="col-jurusan">Jurusan</th>
                <th class="col-telp">No. Telp</th>
                <th class="col-ortu">Pekerjaan Ortu</th>
                <th class="col-penghasilan">Penghasilan Ortu</th>
                <th class="col-alamat">Alamat</th>
                <th class="col-pil1">Pilihan 1</th>
                <th class="col-pil2">Pilihan 2</th>
                <th class="col-status">Status</th>
                <th class="col-tempat">Tempat Diterima</th>
                <th class="col-penempatan">Penempatan</th>
                <th class="col-tanggal">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $index => $p)
                @php
                    $penempatan = $p->penempatan;
                    $statusClass = match($p->status) {
                        'diterima' => 'badge-diterima',
                        'ditolak'  => 'badge-ditolak',
                        default    => 'badge-pending',
                    };
                    $penempatanClass = 'badge-belum';
                    if ($penempatan) {
                        $penempatanClass = match($penempatan->status) {
                            'aktif'   => 'badge-aktif',
                            'selesai' => 'badge-selesai',
                            default   => 'badge-batal',
                        };
                    }
                @endphp
                <tr>
                    <td class="col-no">{{ $index + 1 }}</td>
                    <td class="col-nis">{{ $p->siswa->nis ?? '-' }}</td>
                    <td class="col-nama">{{ $p->siswa->nama_siswa ?? '-' }}</td>
                    <td class="col-jurusan">{{ $p->jurusan ?? '-' }}</td>
                    <td class="col-telp">{{ $p->siswa->no_telepon ?? '-' }}</td>
                    <td class="col-ortu">{{ $p->pekerjaan_orang_tua ?: '-' }}</td>
                    <td class="col-penghasilan">
                        @if ($p->penghasilan_ortu)
                            Rp {{ number_format((int) str_replace('.', '', $p->penghasilan_ortu), 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="col-alamat">{{ $p->alamat ?: '-' }}</td>
                    <td class="col-pil1">
                        {{ $p->pilihan_1 }}
                        @if ($p->industri_1)
                            <br><span style="color:#555;">({{ $p->industri_1 }})</span>
                        @endif
                    </td>
                    <td class="col-pil2">
                        {{ $p->pilihan_2 }}
                        @if ($p->industri_2)
                            <br><span style="color:#555;">({{ $p->industri_2 }})</span>
                        @endif
                    </td>
                    <td class="col-status">
                        <span class="badge {{ $statusClass }}">{{ ucfirst($p->status) }}</span>
                    </td>
                    <td class="col-tempat">{{ $p->tempat_diterima ?: '-' }}</td>
                    <td class="col-penempatan">
                        @if ($penempatan)
                            <strong>{{ $penempatan->industri->nama_perusahaan ?? '-' }}</strong><br>
                            Guru: {{ $penempatan->guru->nama_guru ?? '-' }}<br>
                            Komp: {{ $penempatan->kompetensi->nama_kompetensi ?? '-' }}<br>
                            {{ \Carbon\Carbon::parse($penempatan->tanggal_mulai)->format('d/m/Y') }}
                            s/d
                            {{ \Carbon\Carbon::parse($penempatan->tanggal_selesai)->format('d/m/Y') }}<br>
                            <span class="badge {{ $penempatanClass }}">{{ ucfirst($penempatan->status) }}</span>
                        @else
                            <span style="color:#888;font-style:italic;">Belum ditempatkan</span>
                        @endif
                    </td>
                    <td class="col-tanggal">
                        {{ $p->created_at ? $p->created_at->format('d/m/Y') : '-' }}
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="14">Tidak ada data pengajuan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak dari Sistem PKL &copy; {{ date('Y') }} — Total: {{ $pengajuans->count() }} pengajuan</p>
    </div>
</body>
</html>