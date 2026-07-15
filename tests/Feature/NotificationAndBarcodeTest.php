<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notifikasi;
use App\Models\TarikTunai;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationAndBarcodeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_barcode_endpoint_returns_base64_successfully(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_user_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'nasabah',
            'status' => 'approved',
            'kode_nasabah' => 'NSB999',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/barcode/nasabah/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'id' => $user->id,
            ]);

        $barcode = $response->json('barcode');
        $this->assertNotEmpty($barcode);
        
        // Ensure it is single base64-encoded PNG by decoding and checking for the PNG magic header
        $decoded = base64_decode($barcode, true);
        $this->assertNotFalse($decoded);
        $this->assertStringStartsWith(hex2bin('89504e47'), $decoded, "The decoded content must start with the PNG signature");
    }

    public function test_notification_endpoints_require_authentication(): void
    {
        $response1 = $this->getJson('/api/notifikasi-kurir');
        $response1->assertStatus(401);

        $response2 = $this->getJson('/api/notifikasi-nasabah');
        $response2->assertStatus(401);
    }

    public function test_notification_endpoints_retrieve_authenticated_user_notifications(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_user_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'nasabah',
            'status' => 'approved',
            'kode_nasabah' => 'NSB999',
        ]);

        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other_user_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'nasabah',
            'status' => 'approved',
            'kode_nasabah' => 'NSB888',
        ]);

        // Create notification for user
        $notif1 = Notifikasi::create([
            'user_id' => $user->id,
            'judul' => 'Info Baru',
            'pesan' => 'Jadwal jemput telah dibuat',
            'type' => 'info',
        ]);

        // Create notification for another user
        $notif2 = Notifikasi::create([
            'user_id' => $otherUser->id,
            'judul' => 'Info Lain',
            'pesan' => 'Lain-lain',
            'type' => 'info',
        ]);

        Sanctum::actingAs($user);

        // Fetch notifications for user (nasabah)
        $response = $this->getJson('/api/notifikasi-nasabah');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 1,
            ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($notif1->id, $response->json('data.0.id'));
    }

    public function test_mark_notification_as_read_successfully(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_user_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'nasabah',
            'status' => 'approved',
            'kode_nasabah' => 'NSB999',
        ]);

        $notif = Notifikasi::create([
            'user_id' => $user->id,
            'judul' => 'Info Baru',
            'pesan' => 'Jadwal jemput telah dibuat',
            'type' => 'info',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/notifikasi/{$notif->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notifikasi berhasil ditandai sebagai dibaca.'
            ]);

        $this->assertNotNull($notif->fresh()->read_at);
        $this->assertEquals(1, $notif->fresh()->is_read);
    }

    public function test_tarik_tunai_riwayat_lengkap_works_correctly(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_user_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'nasabah',
            'status' => 'approved',
            'kode_nasabah' => 'NSB999',
        ]);

        $tarik = TarikTunai::create([
            'user_id' => $user->id,
            'jumlah_nominal' => 50000,
            'status' => 'pending',
            'tanggal_request' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tarik-tunai/riwayat-lengkap');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertEquals($tarik->id, $response->json('0.id'));
    }
}
