<?php

namespace Database\Seeders;

use App\Models\MediaOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin Tim Publikasi Humas
        User::firstOrCreate(
            ['email' => 'admin@dprd-tangerangkota.go.id'],
            [
                'name' => 'Tim Publikasi Humas DPRD',
                'password' => Hash::make('password123'),
            ]
        );

        // Sample Orders
        $today = Carbon::today();

        if (MediaOrder::count() === 0) {
            MediaOrder::create([
                'kode_booking' => 'HUMAS-' . date('Ym') . '-001',
                'nama_pemohon' => 'H. Rusdi Alam, M.Si',
                'fraksi' => 'Golkar',
                'no_whatsapp' => '081289123456',
                'nama_kegiatan' => 'Kunjungan Kerja Pimpinan DPRD ke Balai Kota Tangerang',
                'kategori' => 'Pimpinan',
                'tanggal_kegiatan' => $today->copy()->format('Y-m-d'),
                'waktu_mulai' => '09:00:00',
                'waktu_disetujui' => '09:00:00',
                'jenis_publikasi' => 'Liputan (Rilis Website, Video Liputan untuk media sosial DPRD)',
                'status' => 'approved',
                'catatan_admin' => 'Tim 1 (Kameramen & Penulis Rilis) meluncur pukul 08.30 WIB.',
            ]);

            MediaOrder::create([
                'kode_booking' => 'HUMAS-' . date('Ym') . '-002',
                'nama_pemohon' => 'Andri S. Permana, S.Sos',
                'fraksi' => 'PDIP',
                'no_whatsapp' => '081312345678',
                'nama_kegiatan' => 'Rapat Dengar Pendapat (RDP) Komisi II terkait UMKM',
                'kategori' => 'Komisi II',
                'tanggal_kegiatan' => $today->copy()->addDay()->format('Y-m-d'),
                'waktu_mulai' => '13:30:00',
                'waktu_disetujui' => '13:30:00',
                'jenis_publikasi' => 'Dokumentasi (Foto/Video)',
                'status' => 'approved',
                'catatan_admin' => 'Fotografer standby di Ruang Rapat Komisi II.',
            ]);

            MediaOrder::create([
                'kode_booking' => 'HUMAS-' . date('Ym') . '-003',
                'nama_pemohon' => 'Tengku Iwan Jayasyah Putra',
                'fraksi' => 'PKS',
                'no_whatsapp' => '081598765432',
                'nama_kegiatan' => 'Sosialisasi Perda Kawasan Tanpa Rokok di Ciledug',
                'kategori' => 'Komisi IV',
                'tanggal_kegiatan' => $today->copy()->addDays(2)->format('Y-m-d'),
                'waktu_mulai' => '10:00:00',
                'waktu_disetujui' => null,
                'jenis_publikasi' => 'Rilis Website',
                'status' => 'pending',
                'catatan_admin' => null,
            ]);
        }
    }
}
