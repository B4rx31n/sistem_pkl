<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cegah penghapusan massal (data loss) via ON DELETE CASCADE.
     *
     * Sebelumnya: hapus User -> cascade hapus Guru/Siswa/Industri ->
     * cascade hapus penempatan_pkl -> cascade hapus jurnal, nilai,
     * komentar, kesimpulan. Artinya menghapus 1 akun bisa menghapus
     * SELURUH riwayat PKL tanpa peringatan.
     *
     * Sekarang: penempatan_pkl memakai RESTRICT, sehingga user/industri/
     * kompetensi yang masih dipakai tidak bisa dihapus (controller
     * menampilkan pesan yang jelas), dan riwayat tetap utuh.
     */
    public function up(): void
    {
        Schema::table('penempatan_pkl', function (Blueprint $table) {
            $table->dropForeign(['siswa_id']);
            $table->dropForeign(['industri_id']);
            $table->dropForeign(['guru_id']);
            $table->dropForeign(['kompetensi_id']);

            $table->foreign('siswa_id')->references('id')->on('siswas')->restrictOnDelete();
            $table->foreign('industri_id')->references('id')->on('industris')->restrictOnDelete();
            $table->foreign('guru_id')->references('id')->on('gurus')->restrictOnDelete();
            $table->foreign('kompetensi_id')->references('id')->on('kompetensis')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penempatan_pkl', function (Blueprint $table) {
            $table->dropForeign(['siswa_id']);
            $table->dropForeign(['industri_id']);
            $table->dropForeign(['guru_id']);
            $table->dropForeign(['kompetensi_id']);

            $table->foreign('siswa_id')->references('id')->on('siswas')->onDelete('cascade');
            $table->foreign('industri_id')->references('id')->on('industris')->onDelete('cascade');
            $table->foreign('guru_id')->references('id')->on('gurus')->onDelete('cascade');
            $table->foreign('kompetensi_id')->references('id')->on('kompetensis')->onDelete('cascade');
        });
    }
};
