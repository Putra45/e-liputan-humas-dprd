<?php

namespace App\Http\Controllers;

use App\Models\MediaOrder;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = MediaOrder::query();

        // Filters
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_kegiatan', $request->tanggal);
        }

        if ($request->filled('fraksi') && $request->fraksi !== 'all') {
            $query->where('fraksi', $request->fraksi);
        }

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_pemohon', 'like', "%{$s}%")
                  ->orWhere('nama_kegiatan', 'like', "%{$s}%")
                  ->orWhere('kode_booking', 'like', "%{$s}%")
                  ->orWhere('no_whatsapp', 'like', "%{$s}%");
            });
        }

        $orders = $query->orderByDesc('tanggal_kegiatan')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Stats
        $stats = [
            'total' => MediaOrder::count(),
            'pending' => MediaOrder::where('status', 'pending')->count(),
            'approved' => MediaOrder::where('status', 'approved')->count(),
            'rejected' => MediaOrder::where('status', 'rejected')->count(),
            'today' => MediaOrder::whereDate('tanggal_kegiatan', Carbon::today())
                ->where('status', 'approved')
                ->count(),
        ];

        $fraksiList = [
            'Golkar', 'PDIP', 'Gerindra', 'PKS', 'PKB', 'Demokrat', 'PAN', 'PSI', 'NasDem', 'PPP', 'Lainnya',
        ];

        $kategoriList = [
            'Pimpinan', 'Komisi I', 'Komisi II', 'Komisi III', 'Komisi IV', 'AKD',
        ];

        return view('admin.orders.index', compact('orders', 'stats', 'fraksiList', 'kategoriList'));
    }

    public function show(MediaOrder $order, WhatsAppService $wa)
    {
        return response()->json([
            'order' => $order,
            'tanggal_formatted' => $order->tanggal_kegiatan ? $order->tanggal_kegiatan->isoFormat('dddd, D MMMM Y') : '-',
            'waktu_mulai_formatted' => substr($order->waktu_mulai, 0, 5),
            'waktu_disetujui_formatted' => $order->waktu_disetujui ? substr($order->waktu_disetujui, 0, 5) : substr($order->waktu_mulai, 0, 5),
            'file_url' => $order->file_dokumen ? Storage::url($order->file_dokumen) : null,
            'manual_wa_link' => $wa->getManualLink($order),
        ]);
    }

    public function updateStatus(Request $request, MediaOrder $order, WhatsAppService $wa)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,pending'],
            'waktu_disetujui' => ['nullable'],
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->status = $validated['status'];
        if ($validated['status'] === 'approved' && !empty($validated['waktu_disetujui'])) {
            $order->waktu_disetujui = $validated['waktu_disetujui'];
        } elseif ($validated['status'] === 'pending') {
            $order->waktu_disetujui = null;
        }
        $order->catatan_admin = $validated['catatan_admin'] ?? null;
        $order->save();

        // Dispatch WA Notification
        $waResult = $wa->send($order);

        $statusText = $order->status === 'approved' ? 'disetujui' : ($order->status === 'rejected' ? 'ditolak' : 'diubah ke pending');

        $flashData = [
            'success' => "Pemesanan {$order->kode_booking} berhasil {$statusText}." . ($waResult['gateway_sent'] ? ' Notifikasi WhatsApp otomatis terkirim!' : ''),
            'wa_manual_link' => $wa->getManualLink($order),
            'wa_pemohon' => $order->nama_pemohon,
        ];

        if (!$waResult['gateway_sent']) {
            $flashData['warning'] = $waResult['message'];
        }

        return redirect()->back()->with($flashData);
    }

    public function destroy(MediaOrder $order)
    {
        if ($order->file_dokumen && Storage::disk('public')->exists($order->file_dokumen)) {
            Storage::disk('public')->delete($order->file_dokumen);
        }

        $order->delete();

        return redirect()->back()->with('success', 'Data pesanan berhasil dihapus.');
    }
}
