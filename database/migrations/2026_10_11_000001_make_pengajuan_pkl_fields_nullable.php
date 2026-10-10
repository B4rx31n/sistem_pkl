<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom ini opsional di form pengajuan siswa (boleh dikosongkan),
     * jadi harus nullable. Sebelumnya NOT NULL sehingga insert dengan
     * nilai kosong gagal (SQLSTATE 23000 - Column cannot be null).
     */
    public function up(): void
    {
        Schema::table('pengajuan_pkl', function (Blueprint $table) {
            $table->string('pekerjaan_orang_tua')->nullable()->change();
            $table->string('penghasilan_ortu')->nullable()->change();
            $table->string('alamat')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_pkl', function (Blueprint $table) {
            $table->string('pekerjaan_orang_tua')->nullable(false)->default('')->change();
            $table->string('penghasilan_ortu')->nullable(false)->default('')->change();
            $table->string('alamat')->nullable(false)->default('')->change();
        });
    }
};
