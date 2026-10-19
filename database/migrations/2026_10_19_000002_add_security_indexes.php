<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CEGAH duplikat jurnal per hari
        Schema::table('jurnal_harian', function (Blueprint $table) {
            // (penempatan_id, tanggal) — sudah dicek di controller, tapi
            // harus ada unique untuk mencegah race condition
            $table->unique(['penempatan_id', 'tanggal'], 'jurnal_penempatan_tanggal_unique');
        });

        // CEGAH duplikat nilai untuk (penempatan, aspek, role_penilai)
        Schema::table('monitoring_nilai', function (Blueprint $table) {
            $table->unique(
                ['penempatan_id', 'aspek_penilaian', 'role_penilai'],
                'monitoring_unique'
            );
            $table->index(['penempatan_id', 'role_penilai'], 'monitoring_penempatan_role');
            $table->index(['role_penilai', 'tanggal_penilaian'], 'monitoring_role_tanggal');
        });

        // Index untuk filtering performa
        Schema::table('penempatan_pkl', function (Blueprint $table) {
            $table->index('status', 'penempatan_status');
            $table->index(['siswa_id', 'status'], 'penempatan_siswa_status');
            $table->index(['guru_id', 'status'], 'penempatan_guru_status');
            $table->index(['industri_id', 'status'], 'penempatan_industri_status');
        });

        Schema::table('template_penilaian', function (Blueprint $table) {
            $table->index(['kategori', 'jurusan', 'is_active'], 'template_filter');
        });

        Schema::table('nilai_kesimpulan', function (Blueprint $table) {
            $table->unique(['penempatan_id', 'guru_id'], 'kesimpulan_unique');
        });
    }

    public function down(): void
    {
        Schema::table('nilai_kesimpulan', function (Blueprint $table) {
            $table->dropUnique('kesimpulan_unique');
        });

        Schema::table('template_penilaian', function (Blueprint $table) {
            $table->dropIndex('template_filter');
        });

        Schema::table('penempatan_pkl', function (Blueprint $table) {
            $table->dropIndex('penempatan_status');
            $table->dropIndex('penempatan_siswa_status');
            $table->dropIndex('penempatan_guru_status');
            $table->dropIndex('penempatan_industri_status');
        });

        Schema::table('monitoring_nilai', function (Blueprint $table) {
            $table->dropUnique('monitoring_unique');
            $table->dropIndex('monitoring_penempatan_role');
            $table->dropIndex('monitoring_role_tanggal');
        });

        Schema::table('jurnal_harian', function (Blueprint $table) {
            $table->dropUnique('jurnal_penempatan_tanggal_unique');
        });
    }
};
