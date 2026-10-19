<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // 'role' sengaja TIDAK dimasukkan ke $fillable agar tidak bisa
    // dimanipulasi lewat mass-assignment. Set role lewat forceFill() eksplisit.
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi ke Guru
    public function guru()
    {
        return $this->hasOne(Guru::class);
    }

    // Relasi ke Siswa
    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    // Relasi ke Industri
    public function industri()
    {
        return $this->hasOne(Industri::class);
    }

    // Cek Role
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isGuru()
    {
        return $this->role === 'guru';
    }

    public function isSiswa()
    {
        return $this->role === 'siswa';
    }

    public function isIndustri()
    {
        return $this->role === 'industri';
    }
}
