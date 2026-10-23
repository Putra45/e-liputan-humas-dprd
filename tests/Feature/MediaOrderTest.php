<?php

namespace Tests\Feature;

use App\Models\MediaOrder;
use App\Models\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_page_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Pemesanan Peliputan Tim Humas DPRD Kota Tangerang');
    }

    public function test_events_api_returns_json(): void
    {
        MediaOrder::create([
            'kode_booking' => 'HUMAS-202610-001',
            'nama_pemohon' => 'Anggota Dewan A',
            'no_whatsapp' => '081234567890',
            'nama_kegiatan' => 'Kunjungan Kerja',
            'kategori' => 'Pimpinan',
            'tanggal_kegiatan' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '10:00:00',
            'jenis_publikasi' => 'Liputan (Rilis Website, Video Liputan untuk media sosial DPRD)',
            'status' => 'approved',
        ]);

        $response = $this->getJson('/api/events');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'nama_pemohon' => 'Anggota Dewan A',
            'nama_kegiatan' => 'Kunjungan Kerja',
        ]);
    }

    public function test_can_submit_order_from_portal(): void
    {
        $response = $this->post('/pesan', [
            'nama_kegiatan' => 'Rapat Paripurna Istimewa',
            'kategori' => 'Pimpinan',
            'tanggal_kegiatan' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '09:00',
            'jenis_publikasi' => 'Liputan (Rilis Website, Video Liputan untuk media sosial DPRD)',
            'nama_pemohon' => 'H. Rusdi Alam',
            'no_whatsapp' => '081289123456',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');

        $this->assertDatabaseHas('media_orders', [
            'nama_pemohon' => 'H. Rusdi Alam',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_login_and_access_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@dprd-tangerangkota.go.id',
            'password' => bcrypt('password123'),
        ]);

        $loginResponse = $this->post('/login', [
            'email' => 'admin@dprd-tangerangkota.go.id',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect(route('admin.orders.index'));
        $this->assertAuthenticatedAs($user);

        $dashResponse = $this->actingAs($user)->get('/admin/orders');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Dashboard Verifikasi Permohonan Liputan');
    }

    public function test_admin_can_approve_order_with_adjusted_time(): void
    {
        $user = User::factory()->create();

        $order = MediaOrder::create([
            'kode_booking' => 'HUMAS-202610-999',
            'nama_pemohon' => 'Dewan B',
            'fraksi' => 'PDIP',
            'no_whatsapp' => '081234567890',
            'nama_kegiatan' => 'Sidak Lapangan',
            'kategori' => 'Komisi I',
            'tanggal_kegiatan' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '09:00:00',
            'jenis_publikasi' => 'Dokumentasi (Foto/Video)',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->patch("/admin/orders/{$order->id}/status", [
            'status' => 'approved',
            'waktu_disetujui' => '10:30',
            'catatan_admin' => 'Jadwal dimundurkan 30 menit karena agenda komisi padat.',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('media_orders', [
            'id' => $order->id,
            'status' => 'approved',
            'waktu_disetujui' => '10:30',
            'catatan_admin' => 'Jadwal dimundurkan 30 menit karena agenda komisi padat.',
        ]);
    }

    public function test_whatsapp_service_formats_phone_and_manual_link(): void
    {
        $wa = new WhatsAppService();
        $this->assertEquals('6281234567890', WhatsAppService::formatPhoneNumber('081234567890'));
        $this->assertEquals('6281234567890', WhatsAppService::formatPhoneNumber('81234567890'));

        $order = new MediaOrder([
            'kode_booking' => 'HUMAS-202610-001',
            'nama_pemohon' => 'Bapak Dewan',
            'fraksi' => 'Golkar',
            'no_whatsapp' => '081234567890',
            'nama_kegiatan' => 'Rapat Paripurna',
            'kategori' => 'Pimpinan',
            'tanggal_kegiatan' => Carbon::tomorrow(),
            'waktu_mulai' => '10:00:00',
            'waktu_disetujui' => '10:30:00',
            'jenis_publikasi' => 'Dokumentasi (Foto/Video)',
            'status' => 'approved',
            'catatan_admin' => 'Fotografer standby.',
        ]);

        $link = $wa->getManualLink($order);
        $this->assertStringContainsString('https://wa.me/6281234567890', $link);
        $this->assertStringContainsString('DISETUJUI', urldecode($link));
    }
}
