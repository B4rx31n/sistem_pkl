<?php

namespace Tests\Feature;

use App\Models\PengajuanPkl;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengajuanIndustriTest extends TestCase
{
    use RefreshDatabase;

    private function siswaUser(): User
    {
        $user = User::factory()->create(['role' => 'siswa']);
        Siswa::factory()->create(['user_id' => $user->id, 'jurusan' => 'XII TKJ 1']);

        return $user;
    }

    public function test_create_page_shows_wilayah_options(): void
    {
        $response = $this->actingAs($this->siswaUser())->get(route('siswa.pengajuan.create'));

        $response->assertOk();
        // 3 wilayah tetap tampil sebagai pilihan
        $response->assertSee('Padang');
        $response->assertSee('Bukittinggi');
        $response->assertSee('Padang Panjang');
        // Wilayah lama yang tidak dipakai lagi tidak tampil
        $response->assertDontSee('Bandung');
        $response->assertDontSee('Medan');
    }

    public function test_store_saves_pilihan_tempat(): void
    {
        $user = $this->siswaUser();

        $response = $this->actingAs($user)->post(route('siswa.pengajuan.store'), [
            'pilihan_1' => 'Padang',
            'pilihan_2' => 'Bukittinggi',
            'no_telepon' => '08123456789',
            'jurusan' => 'TKJ',
            'penghasilan_ortu' => 3000000,
        ]);

        $response->assertRedirect(route('siswa.pengajuan.index'));

        $pengajuan = PengajuanPkl::where('siswa_id', $user->siswa->id)->first();
        $this->assertNotNull($pengajuan);
        $this->assertEquals('Padang', $pengajuan->pilihan_1);
        $this->assertEquals('Bukittinggi', $pengajuan->pilihan_2);
        $this->assertEquals('pending', $pengajuan->status);
    }

    public function test_admin_can_update_logo_via_settings(): void
    {
        Storage::fake('public');

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        Storage::disk('local')->put('test-logo.png', $png);
        $file = new UploadedFile(Storage::disk('local')->path('test-logo.png'), 'logo.png', 'image/png', null, true);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'logo' => $file,
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertNotNull(Setting::get('logo_path'));
        Storage::disk('public')->assertExists(Setting::get('logo_path'));
    }
}
