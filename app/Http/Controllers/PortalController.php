<?php

namespace App\Http\Controllers;

use App\Models\MediaOrder;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortalController extends Controller
{
    public function index()
    {
        $fraksiList = [
            'Golkar',
            'PDIP',
            'Gerindra',
            'PKS',
            'PKB',
            'Demokrat',
            'PAN',
            'PSI',
            'NasDem',
            'PPP',
            'Lainnya',
        ];

        $kategoriList = [
            'Pimpinan',
            'Komisi I',
            'Komisi II',
            'Komisi III',
            'Komisi IV',
            'AKD',
        ];

        $jenisPublikasiList = [
            'Liputan (Rilis Website, Video Liputan untuk media sosial DPRD)',
            'Dokumentasi (Foto/Video)',
            'Rilis Website',
            'Konten Media Sosial (Kegiatan pribadi)',
        ];

        // Stats summary for quick glance on portal
        $todayApprovedCount = MediaOrder::whereDate('tanggal_kegiatan', Carbon::today())
            ->where('status', 'approved')
            ->count();

        return view('portal.index', compact('fraksiList', 'kategoriList', 'jenisPublikasiList', 'todayApprovedCount'));
    }

    public function events()
    {
        // Only return approved and pending events; hide personal contact & documents for privacy
        $orders = MediaOrder::whereIn('status', ['approved', 'pending'])
            ->get();

        $events = $orders->map(function ($order) {
            $waktu = $order->waktu_disetujui ? substr($order->waktu_disetujui, 0, 5) : substr($order->waktu_mulai, 0, 5);
            $isApproved = $order->status === 'approved';

            return [
                'id' => $order->id,
                'title' => "[$waktu] {$order->nama_pemohon} ({$order->fraksi})",
                'start' => $order->tanggal_kegiatan->format('Y-m-d') . 'T' . $waktu . ':00',
                'backgroundColor' => $isApproved ? '#0f2e5a' : '#d97706',
                'borderColor' => $isApproved ? '#0a1d37' : '#b45309',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'kode_booking' => $order->kode_booking,
                    'nama_pemohon' => $order->nama_pemohon,
                    'fraksi' => $order->fraksi,
                    'kategori' => $order->kategori,
                    'nama_kegiatan' => $order->nama_kegiatan,
                    'waktu' => $waktu . ' WIB',
                    'jenis_publikasi' => $order->jenis_publikasi,
                    'status' => $order->status,
                    'status_label' => $order->status_badge['label'],
                ],
            ];
        });

        return response()->json($events);
    }

    public function store(Request $request, WhatsAppService $wa)
    {
        $validated = $request->validate([
            'nama_pemohon' => ['required', 'string', 'max:255'],
            'fraksi' => ['required', 'string', 'max:50'],
            'no_whatsapp' => ['required', 'string', 'min:9', 'max:20'],
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:50'],
            'tanggal_kegiatan' => ['required', 'date'],
            'waktu_mulai' => ['required'],
            'jenis_publikasi' => ['required', 'string', 'max:255'],
            'file_dokumen' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ], [
            'nama_pemohon.required' => 'Nama pemohon / Anggota Dewan wajib diisi.',
            'fraksi.required' => 'Fraksi wajib dipilih.',
            'no_whatsapp.required' => 'Nomor WhatsApp wajib diisi untuk konfirmasi.',
            'nama_kegiatan.required' => 'Nama kegiatan wajib diisi.',
            'kategori.required' => 'Kategori kegiatan wajib dipilih.',
            'tanggal_kegiatan.required' => 'Tanggal kegiatan wajib diisi.',
            'waktu_mulai.required' => 'Waktu kegiatan wajib diisi.',
            'jenis_publikasi.required' => 'Jenis publikasi wajib dipilih.',
            'file_dokumen.max' => 'Ukuran dokumen maksimal 10 MB.',
        ]);

        $filePath = null;
        if ($request->hasFile('file_dokumen')) {
            $filePath = $request->file('file_dokumen')->store('dokumen_liputan', 'public');
        }

        $order = MediaOrder::create([
            'kode_booking' => MediaOrder::generateKodeBooking(),
            'nama_pemohon' => $validated['nama_pemohon'],
            'fraksi' => $validated['fraksi'],
            'no_whatsapp' => $validated['no_whatsapp'],
            'nama_kegiatan' => $validated['nama_kegiatan'],
            'kategori' => $validated['kategori'],
            'tanggal_kegiatan' => $validated['tanggal_kegiatan'],
            'waktu_mulai' => $validated['waktu_mulai'],
            'jenis_publikasi' => $validated['jenis_publikasi'],
            'file_dokumen' => $filePath,
            'status' => 'pending',
        ]);

        $waResult = $wa->send($order);

        return redirect()->route('portal.index')->with([
            'success_order' => $order,
            'wa_manual_link' => $wa->getManualLink($order),
        ]);
    }
}
