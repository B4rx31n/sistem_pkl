@extends('layouts.app')

@section('title', 'Form Pengajuan PKL')
@section('page-title', 'Pengajuan PKL')
@section('page-subtitle', 'Isi form pengajuan tempat PKL')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5>Form Pengajuan Tempat PKL</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('siswa.pengajuan.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <!-- NIS -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">NIS</label>
                            <input type="text" class="form-control" value="{{ $siswa->nis }}" disabled>
                        </div>

                        <!-- Nama Siswa -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nama Siswa</label>
                            <input type="text" class="form-control" value="{{ $siswa->nama_siswa }}" disabled>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Jurusan</label>
                            <input type="text" class="form-control" value="{{ $siswa->jurusan }}" disabled>
                        </div>

                        <!-- No Telepon -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">No Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" name="no_telepon"
                                class="form-control @error('no_telepon') is-invalid @enderror"
                                value="{{ old('no_telepon', $siswa->no_telepon) }}" placeholder="Contoh: 08123456789">
                            <small class="text-muted">Nomor HP aktif yang bisa dihubungi</small>
                            @error('no_telepon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Pilihan Tempat PKL 1 -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Pilihan Tempat PKL 1 <span class="text-danger">*</span></label>
                            <select name="pilihan_1" id="pilihan_1_select"
                                class="form-select @error('pilihan_1') is-invalid @enderror" onchange="toggleLainnya(1)">
                                <option value="">Pilih Tempat</option>
                                @foreach ($lokasiList as $lokasi)
                                    <option value="{{ $lokasi }}"
                                        {{ old('pilihan_1') == $lokasi ? 'selected' : '' }}>{{ $lokasi }}</option>
                                @endforeach
                                <option value="Lainnya" {{ old('pilihan_1') == 'Lainnya' ? 'selected' : '' }}>Lainnya
                                    (tulis sendiri)</option>
                            </select>
                            <input type="text" name="pilihan_1" id="pilihan_1_input"
                                class="form-control @error('pilihan_1') is-invalid @enderror d-none"
                                placeholder="Tulis tempat PKL lainnya..."
                                value="{{ old('pilihan_1') == 'Lainnya' ? '' : old('pilihan_1') }}" disabled>
                            @error('pilihan_1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Industri Pilihan 1 -->
                        {{-- <div class="col-md-12 mb-3 d-none industri-box" id="industri_1_wrapper">
                            <label class="form-label">Industri Pilihan 1</label>

                            <select name="industri_1" id="industri_1_select"
                                class="form-select @error('industri_1') is-invalid @enderror"
                                onchange="toggleIndustriLainnya(1)">
                                <option value="">Pilih Industri</option>
                                <option value="Lainnya">Lainnya (tulis sendiri)</option>
                            </select>

                            <input type="text" name="industri_1" id="industri_1_input"
                                class="form-control @error('industri_1') is-invalid @enderror d-none"
                                placeholder="Tulis nama industri lainnya...">

                            @error('industri_1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> --}}

                        <!-- Pilihan Tempat PKL 2 -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Pilihan Tempat PKL 2 <span class="text-danger">*</span></label>
                            <select name="pilihan_2" id="pilihan_2_select"
                                class="form-select @error('pilihan_2') is-invalid @enderror" onchange="toggleLainnya(2)">
                                <option value="">Pilih Tempat</option>
                                @foreach ($lokasiList as $lokasi)
                                    <option value="{{ $lokasi }}"
                                        {{ old('pilihan_2') == $lokasi ? 'selected' : '' }}>{{ $lokasi }}</option>
                                @endforeach
                                <option value="Lainnya" {{ old('pilihan_2') == 'Lainnya' ? 'selected' : '' }}>Lainnya
                                    (tulis sendiri)</option>
                            </select>
                            <input type="text" name="pilihan_2" id="pilihan_2_input"
                                class="form-control @error('pilihan_2') is-invalid @enderror d-none"
                                placeholder="Tulis tempat PKL lainnya..."
                                value="{{ old('pilihan_2') == 'Lainnya' ? '' : old('pilihan_2') }}" disabled>
                            @error('pilihan_2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Industri Pilihan 2 -->
                        {{-- <div class="col-md-12 mb-3 d-none industri-box" id="industri_2_wrapper">
                            <label class="form-label">Industri Pilihan 2</label>

                            <select name="industri_2" id="industri_2_select"
                                class="form-select @error('industri_2') is-invalid @enderror"
                                onchange="toggleIndustriLainnya(2)">
                                <option value="">Pilih Industri</option>
                                <option value="Lainnya">Lainnya (tulis sendiri)</option>
                            </select>

                            <input type="text" name="industri_2" id="industri_2_input"
                                class="form-control @error('industri_2') is-invalid @enderror d-none"
                                placeholder="Tulis nama industri lainnya...">

                            @error('industri_2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> --}}

                        <!-- Pekerjaan Orang Tua -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Pekerjaan Orang Tua</label>
                            <input type="text" name="pekerjaan_orang_tua"
                                class="form-control @error('pekerjaan_orang_tua') is-invalid @enderror"
                                value="{{ old('pekerjaan_orang_tua') }}"
                                placeholder="Contoh: Petani, PNS, Wiraswasta, dll">
                            @error('pekerjaan_orang_tua')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Penghasilan Orang Tua -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Penghasilan Orang Tua (per bulan)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="penghasilan_ortu" id="penghasilan_ortu"
                                    class="form-control @error('penghasilan_ortu') is-invalid @enderror"
                                    value="{{ old('penghasilan_ortu') }}" placeholder="2.000.000">
                            </div>
                            @error('penghasilan_ortu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Alamat Rumah -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Alamat Rumah</label>
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3"
                                placeholder="Masukkan alamat lengkap rumah Anda">{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i> Kirim Pengajuan
                        </button>
                        <a href="{{ route('siswa.pengajuan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleLainnya(n) {
            const select = document.getElementById('pilihan_' + n + '_select');
            const input = document.getElementById('pilihan_' + n + '_input');

            if (!select || !input) return;

            if (select.value === 'Lainnya') {
                // Sembunyikan select tempat PKL, tampilkan input
                select.classList.add('d-none');
                select.disabled = true;

                input.classList.remove('d-none');
                input.disabled = false;
                input.focus();
            } else {
                // Tampilkan select tempat PKL, sembunyikan input
                select.classList.remove('d-none');
                select.disabled = false;

                input.classList.add('d-none');
                input.disabled = true;

                if (select.value !== '') {
                    input.value = '';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            ['1', '2'].forEach(function(n) {
                const select = document.getElementById('pilihan_' + n + '_select');
                const input = document.getElementById('pilihan_' + n + '_input');

                if (!select || !input) return;

                // Kalau ada nilai custom (hasil old()), langsung tampilkan input
                if (select.value === '' && input.value.trim() !== '') {
                    select.classList.add('d-none');
                    select.disabled = true;

                    input.classList.remove('d-none');
                    input.disabled = false;
                } else {
                    toggleLainnya(n);
                }
            });
        });
    </script>
@endpush
