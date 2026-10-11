@extends('layouts.app')

@section('title', 'Daftar Pengajuan PKL')
@section('page-title', 'Pengajuan PKL')
@section('page-subtitle', 'Kelola pengajuan tempat PKL siswa')

@section('content')
    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5>Daftar Pengajuan PKL</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.pengajuan.index') }}" class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-md-4">
                        <label for="jurusan" class="form-label mb-1">Kelas / Jurusan</label>
                        <select name="jurusan" id="jurusan" class="form-select">
                            <option value="">Semua Kelas</option>
                            @foreach ($jurusanList as $jurusan)
                                <option value="{{ $jurusan }}" {{ request('jurusan') == $jurusan ? 'selected' : '' }}>
                                    {{ $jurusan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        @if (request('jurusan'))
                            <a href="{{ route('admin.pengajuan.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Reset
                            </a>
                        @endif
                    </div>
                    <div class="col-12 col-md ms-md-auto text-md-end">
                        <a href="{{ route('admin.pengajuan.export.pdf', request()->all()) }}" class="btn btn-danger">
                            <i class="fas fa-file-pdf me-1"></i> Cetak PDF
                        </a>
                        <a href="{{ route('admin.pengajuan.export.excel', request()->all()) }}" class="btn btn-success">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>No Telepon</th>
                                <th>Pilihan 1</th>
                                <th>Pilihan 2</th>
                                <th>Status Pengajuan</th>
                                <th>Status Penempatan</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengajuans as $index => $p)
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>{{ $p->siswa->nis }}</td>
                                    <td>{{ $p->siswa->nama_siswa }}</td>
                                    <td>{{ $p->jurusan ?? '-' }}</td>
                                    <td>{{ $p->siswa->no_telepon ?? '-' }}</td>
                                    <td>{{ $p->pilihan_1 }}{{ $p->industri_1 ? ' - ' . $p->industri_1 : '' }}</td>
                                    <td>{{ $p->pilihan_2 }}{{ $p->industri_2 ? ' - ' . $p->industri_2 : '' }}</td>
                                    <td>
                                        @if ($p->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($p->status == 'diterima')
                                            <span class="badge bg-success">Diterima</span>
                                        @else
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($p->penempatan_id)
                                            <span class="badge bg-primary">
                                                <i class="fas fa-check-circle me-1"></i> Sudah
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-clock me-1"></i> Belum
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $p->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.pengajuan.show', $p->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">Belum ada pengajuan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $pengajuans->links() }}
            </div>
        </div>
    </div>
@endsection
