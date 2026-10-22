<?php

namespace App\Services;

use App\Models\MediaOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $provider;
    protected ?string $token;
    protected ?string $endpoint;

    public function __construct()
    {
        $this->provider = config('services.whatsapp.provider', 'fonnte');
        $this->token = config('services.whatsapp.token');
        $this->endpoint = config('services.whatsapp.endpoint', 'https://api.fonnte.com/send');
    }

    public static function formatPhoneNumber(string $number): string
    {
        $clean = preg_replace('/[^0-9]/', '', $number);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }
        return $clean;
    }

    public function buildMessage(MediaOrder $order): string
    {
        $tanggalFormatted = $order->tanggal_kegiatan ? $order->tanggal_kegiatan->isoFormat('dddd, D MMMM Y') : '-';
        $waktu = $order->waktu_disetujui ? substr($order->waktu_disetujui, 0, 5) : substr($order->waktu_mulai, 0, 5);

        if ($order->status === 'approved') {
            $msg = "🏛 *TIM PUBLIKASI HUMAS DPRD KOTA TANGERANG*\n";
            $msg .= "----------------------------------------\n";
            $msg .= "Yth. *{$order->nama_pemohon}* ({$order->fraksi})\n\n";
            $msg .= "Permohonan peliputan Anda telah *DISETUJUI (ACC)*.\n\n";
            $msg .= "📋 *Kode Booking:* {$order->kode_booking}\n";
            $msg .= "📌 *Kegiatan:* {$order->nama_kegiatan}\n";
            $msg .= "🏛 *Kategori:* {$order->kategori}\n";
            $msg .= "🗓 *Hari/Tanggal:* {$tanggalFormatted}\n";
            $msg .= "⏰ *Waktu Liputan:* {$waktu} WIB\n";
            $msg .= "🎬 *Jenis Publikasi:* {$order->jenis_publikasi}\n";

            if (!empty($order->catatan_admin)) {
                $msg .= "📝 *Catatan Tim Humas:* {$order->catatan_admin}\n";
            }

            $msg .= "\nTim Liputan/Dokumentasi Humas DPRD Kota Tangerang akan hadir sesuai jadwal di atas.\n";
            $msg .= "Terima kasih.";
            return $msg;
        }

        if ($order->status === 'rejected') {
            $msg = "🏛 *TIM PUBLIKASI HUMAS DPRD KOTA TANGERANG*\n";
            $msg .= "----------------------------------------\n";
            $msg .= "Yth. *{$order->nama_pemohon}* ({$order->fraksi})\n\n";
            $msg .= "Mohon maaf, permohonan peliputan Anda *BELUM DAPAT DISETUJUI*.\n\n";
            $msg .= "📋 *Kode Booking:* {$order->kode_booking}\n";
            $msg .= "📌 *Kegiatan:* {$order->nama_kegiatan}\n";
            $msg .= "🗓 *Tanggal:* {$tanggalFormatted}\n";

            if (!empty($order->catatan_admin)) {
                $msg .= "📝 *Alasan / Keterangan:* {$order->catatan_admin}\n";
            }

            $msg .= "\nSilakan berkoordinasi langsung dengan Tim Humas & Protokol Sekretariat DPRD Kota Tangerang untuk informasi lebih lanjut.\n";
            $msg .= "Terima kasih.";
            return $msg;
        }

        // Default pending confirmation
        $msg = "🏛 *TIM PUBLIKASI HUMAS DPRD KOTA TANGERANG*\n";
        $msg .= "----------------------------------------\n";
        $msg .= "Yth. *{$order->nama_pemohon}* ({$order->fraksi})\n\n";
        $msg .= "Permohonan peliputan Anda telah kami terima dengan status *MENUNGGU KONFIRMASI (PENDING)*.\n\n";
        $msg .= "📋 *Kode Booking:* {$order->kode_booking}\n";
        $msg .= "📌 *Kegiatan:* {$order->nama_kegiatan}\n";
        $msg .= "🗓 *Tanggal:* {$tanggalFormatted} ({$waktu} WIB)\n";
        $msg .= "\nTim Humas akan segera memverifikasi ketersediaan jadwal. Terima kasih.";
        return $msg;
    }

    public function send(MediaOrder $order): array
    {
        $target = self::formatPhoneNumber($order->no_whatsapp);
        $message = $this->buildMessage($order);

        if (empty($this->token)) {
            Log::info("WhatsApp gateway token not configured. Order: {$order->kode_booking}. Fallback URL generated.");
            return [
                'success' => false,
                'gateway_sent' => false,
                'message' => 'Token WhatsApp Gateway belum diisi di .env. Gunakan tombol Kirim WA Manual.',
                'manual_link' => $this->getManualLink($order),
            ];
        }

        try {
            $response = Http::timeout(6)->withHeaders([
                'Authorization' => $this->token,
            ])->post($this->endpoint, [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'gateway_sent' => true,
                    'message' => 'Notifikasi WhatsApp berhasil dikirim ke pemohon.',
                    'manual_link' => $this->getManualLink($order),
                ];
            }

            Log::warning("WhatsApp gateway response error: " . $response->body());
            return [
                'success' => false,
                'gateway_sent' => false,
                'message' => 'Gagal mengirim WA via API (' . $response->status() . '). Gunakan tombol WA Manual.',
                'manual_link' => $this->getManualLink($order),
            ];
        } catch (\Throwable $e) {
            Log::error("WhatsApp gateway exception: " . $e->getMessage());
            return [
                'success' => false,
                'gateway_sent' => false,
                'message' => 'Koneksi ke gateway WA gagal: ' . $e->getMessage(),
                'manual_link' => $this->getManualLink($order),
            ];
        }
    }

    public function getManualLink(MediaOrder $order): string
    {
        $target = self::formatPhoneNumber($order->no_whatsapp);
        $message = $this->buildMessage($order);
        return 'https://wa.me/' . $target . '?text=' . rawurlencode($message);
    }
}
