<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BankSampah;
use App\Models\Mitra;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WebRouteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('login');
    }

    public function test_partner_login_page_renders_successfully(): void
    {
        $response = $this->get(route('partner.login'));
        $response->assertStatus(200);
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_dashboard_works_for_admin_bank(): void
    {
        $bank = BankSampah::create([
            'nama_bank' => 'Test Bank',
            'alamat' => 'Test Alamat',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Admin Bank User',
            'email' => 'admin_bank_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin_bank',
            'status' => 'aktif',
            'bank_sampah_id' => $bank->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    public function test_dlh_dashboard_works_for_admin_dlh(): void
    {
        $bank = BankSampah::create([
            'nama_bank' => 'Test Bank',
            'alamat' => 'Test Alamat',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Admin DLH User',
            'email' => 'admin_dlh_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin_dlh',
            'status' => 'aktif',
            'bank_sampah_id' => $bank->id,
        ]);

        $response = $this->actingAs($user)->get(route('dlh.dashboard'));
        $response->assertStatus(200);
    }

    public function test_partner_dashboard_works_for_mitra(): void
    {
        $mitra = Mitra::create([
            'nama_mitra' => 'Test Mitra',
            'jenis_mitra' => 'Pengepul',
            'penanggung_jawab' => 'John Doe',
            'no_hp' => '081234567890',
            'email' => 'mitra_' . uniqid() . '@example.com',
            'alamat' => 'Test Alamat Mitra',
            'status' => 'Aktif',
        ]);

        $user = User::create([
            'name' => 'Mitra User',
            'email' => $mitra->email,
            'password' => bcrypt('password'),
            'role' => 'mitra',
            'status' => 'aktif',
        ]);

        $mitra->update(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('partner.dashboard'));
        $response->assertStatus(200);
    }
}
