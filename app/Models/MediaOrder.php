<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MediaOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_booking',
        'nama_pemohon',
        'fraksi',
        'no_whatsapp',
        'nama_kegiatan',
        'kategori',
        'tanggal_kegiatan',
        'waktu_mulai',
        'waktu_disetujui',
        'jenis_publikasi',
        'file_dokumen',
        'status',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'date',
    ];

    public static function generateKodeBooking(): string
    {
        $prefix = 'HUMAS-' . date('Ym') . '-';
        $last = self::where('kode_booking', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->kode_booking, -3);
            $next = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $next = '001';
        }

        return $prefix . $next;
    }

    public function getWaktuFinalAttribute(): string
    {
        return $this->waktu_disetujui ? substr($this->waktu_disetujui, 0, 5) : substr($this->waktu_mulai, 0, 5);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'approved' => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-300', 'label' => 'Disetujui'],
            'rejected' => ['bg' => 'bg-rose-100 text-rose-800 border-rose-300', 'label' => 'Ditolak'],
            default => ['bg' => 'bg-amber-100 text-amber-800 border-amber-300', 'label' => 'Pending'],
        };
    }
}
