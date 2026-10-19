<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom password_copy.
     *
     * Kolom ini menyimpan password user dalam bentuk terdekripsi-able
     * (enkrpsi hanya berbasis APP_KEY) sehingga setara menyimpan password
     * plaintext yang bisa dibalikkan. Keamanannya buruk: siapa pun yang
     * mendapatkan backup database + APP_KEY bisa membaca semua password.
     * Gunakan alur "Lupa Password" (reset via email) sebagai gantinya.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_copy');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('password_copy')->nullable()->after('password');
        });
    }
};
