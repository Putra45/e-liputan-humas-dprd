@extends('layouts.app')

@section('title', 'Dashboard Admin Tim Humas DPRD Kota Tangerang')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header & Greetings -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-700">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                Panel Kontrol Publikasi & Dokumentasi
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 mt-1">Dashboard Verifikasi Permohonan Liputan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola antrean permohonan, penyesuaian jadwal personel, dan notifikasi WhatsApp.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('portal.index') }}" target="_blank" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Lihat Portal Dewan
            </a>
        </div>
    </div>

    <!-- Flash Alerts -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-300 rounded-2xl text-emerald-900 text-xs sm:text-sm flex items-start justify-between gap-3 shadow-sm">
        <div class="flex items-start gap-2.5">
            <span class="text-emerald-600 font-bold text-base">✓</span>
            <div>
                <p class="font-bold">{{ session('success') }}</p>
                @if(session('warning'))
                    <p class="text-amber-800 text-xs mt-1 bg-amber-50 p-2 rounded-lg border border-amber-200">
                        ⚠️ {{ session('warning') }}
                    </p>
                @endif
            </div>
        </div>
        @if(session('wa_manual_link'))
            <a href="{{ session('wa_manual_link') }}" target="_blank" class="shrink-0 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow flex items-center gap-1.5 transition">
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                Kirim via WhatsApp Web
            </a>
        @endif
    </div>
    @endif

    <!-- 5 Stats Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-slate-500">Total Permohonan</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm">
                📋
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-amber-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-amber-600">Menunggu (Pending)</p>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                ⏳
            </div>
        </div>

        <!-- Approved -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-emerald-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-emerald-600">Disetujui (ACC)</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['approved'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                ✅
            </div>
        </div>

        <!-- Rejected -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-rose-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-rose-600">Ditolak</p>
                <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ $stats['rejected'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm">
                ❌
            </div>
        </div>

        <!-- Hari Ini -->
        <div class="col-span-2 sm:col-span-1 bg-gradient-to-br from-[#0f2e5a] to-[#0a1d37] text-white p-4 sm:p-5 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-amber-300">Agenda Hari Ini</p>
                <p class="text-2xl font-extrabold text-white mt-1">{{ $stats['today'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-white/10 text-amber-400 flex items-center justify-center font-bold text-sm">
                📅
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <!-- Search -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Cari Pemohon / Kegiatan</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kata kunci..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:border-blue-600 bg-white">
                    <option value="all">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui (ACC)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Fraksi Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Fraksi</label>
                <select name="fraksi" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:border-blue-600 bg-white">
                    <option value="all">Semua Fraksi</option>
                    @foreach($fraksiList as $fr)
                        <option value="{{ $fr }}" {{ request('fraksi') === $fr ? 'selected' : '' }}>{{ $fr }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Tanggal Kegiatan</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:border-blue-600">
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-[#0f2e5a] hover:bg-blue-900 text-white font-bold text-xs rounded-xl transition">
                    Filter
                </button>
                <a href="{{ route('admin.orders.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Booking & Tanggal</th>
                        <th class="py-3.5 px-4">Pemohon & Fraksi</th>
                        <th class="py-3.5 px-4">Kegiatan & Kategori</th>
                        <th class="py-3.5 px-4">Jam Diajukan / ACC</th>
                        <th class="py-3.5 px-4">Dokumen</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $item)
                    <tr class="hover:bg-slate-50/70 transition">
                        <!-- Kode & Tanggal -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="font-mono font-bold text-blue-950 block">{{ $item->kode_booking }}</span>
                            <span class="text-slate-500 text-[11px]">
                                {{ $item->tanggal_kegiatan ? $item->tanggal_kegiatan->isoFormat('D MMM Y') : '-' }}
                            </span>
                        </td>

                        <!-- Pemohon -->
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $item->nama_pemohon }}</div>
                            <div class="text-[11px] text-blue-700 font-medium">Fraksi {{ $item->fraksi }}</div>
                            <div class="text-[11px] text-slate-500 font-mono flex items-center gap-1 mt-0.5">
                                <span>📱</span> {{ $item->no_whatsapp }}
                            </div>
                        </td>

                        <!-- Kegiatan -->
                        <td class="py-3.5 px-4 max-w-xs">
                            <div class="font-medium text-slate-800 line-clamp-2">{{ $item->nama_kegiatan }}</div>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $item->kategori }}
                            </span>
                        </td>

                        <!-- Jam -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="text-slate-600">Diajukan: <span class="font-bold text-slate-900">{{ substr($item->waktu_mulai, 0, 5) }} WIB</span></div>
                            @if($item->waktu_disetujui)
                                <div class="text-emerald-700 font-bold mt-0.5">ACC: {{ substr($item->waktu_disetujui, 0, 5) }} WIB</div>
                            @else
                                <div class="text-slate-400 text-[11px] italic mt-0.5">Belum disesuaikan</div>
                            @endif
                        </td>

                        <!-- Dokumen -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if($item->file_dokumen)
                                <a href="{{ Storage::url($item->file_dokumen) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-[11px] font-medium border border-blue-200 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Lihat File
                                </a>
                            @else
                                <span class="text-slate-400 italic text-[11px]">Tidak ada</span>
                            @endif
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $item->status_badge['bg'] }}">
                                {{ $item->status_badge['label'] }}
                            </span>
                        </td>

                        <!-- Aksi -->
                        <td class="py-3.5 px-4 whitespace-nowrap text-center space-x-1">
                            <button type="button" onclick="openApprovalModal({{ $item->id }})" class="px-3 py-1.5 bg-[#0f2e5a] hover:bg-blue-800 text-white rounded-lg font-bold text-xs transition shadow-sm inline-flex items-center gap-1">
                                <span>⚙️</span> Verifikasi
                            </button>
                            <form action="{{ route('admin.orders.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data permohonan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg font-medium text-xs border border-rose-200 transition" title="Hapus">
                                    🗑️
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                            Tidak ditemukan permohonan peliputan yang sesuai filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Verifikasi & ACC -->
<div id="approvalModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full border border-slate-200 overflow-hidden transform transition-all max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="bg-[#0f2e5a] text-white px-6 py-4 flex items-center justify-between shrink-0">
            <div>
                <h3 class="font-bold text-base">Verifikasi & Keputusan Liputan</h3>
                <p id="modal-subheading" class="text-xs text-blue-200 mt-0.5"></p>
            </div>
            <button type="button" onclick="closeApprovalModal()" class="text-slate-300 hover:text-white rounded-lg p-1 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body Form -->
        <form id="approvalForm" method="POST" class="overflow-y-auto p-6 space-y-5 text-xs sm:text-sm">
            @csrf
            @method('PATCH')

            <!-- Detail Permohonan Card -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                <div class="font-bold text-slate-800 text-sm" id="detail-kegiatan"></div>
                <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 pt-1">
                    <div>Pemohon: <strong class="text-slate-900" id="detail-pemohon"></strong></div>
                    <div>Fraksi: <strong class="text-blue-800" id="detail-fraksi"></strong></div>
                    <div>Tanggal: <strong class="text-slate-900" id="detail-tanggal"></strong></div>
                    <div>Kategori: <strong class="text-slate-900" id="detail-kategori"></strong></div>
                    <div>Waktu Diajukan: <strong class="text-slate-900" id="detail-waktu-mulai"></strong></div>
                    <div>Publikasi: <strong class="text-slate-900" id="detail-jenis"></strong></div>
                </div>

                <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-mono" id="detail-kontak"></span>
                    <span id="detail-file-container"></span>
                </div>
            </div>

            <!-- Keputusan Status -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Keputusan Status <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="flex items-center gap-2 p-2.5 border border-slate-200 rounded-xl cursor-pointer hover:bg-emerald-50/50 transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-900 font-bold text-xs">
                        <input type="radio" name="status" value="approved" id="status-approved" class="text-emerald-600 focus:ring-emerald-500">
                        <span>Setujui (ACC)</span>
                    </label>

                    <label class="flex items-center gap-2 p-2.5 border border-slate-200 rounded-xl cursor-pointer hover:bg-rose-50/50 transition has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 has-[:checked]:text-rose-900 font-bold text-xs">
                        <input type="radio" name="status" value="rejected" id="status-rejected" class="text-rose-600 focus:ring-rose-500">
                        <span>Tolak</span>
                    </label>

                    <label class="flex items-center gap-2 p-2.5 border border-slate-200 rounded-xl cursor-pointer hover:bg-amber-50/50 transition has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-900 font-bold text-xs">
                        <input type="radio" name="status" value="pending" id="status-pending" class="text-amber-600 focus:ring-amber-500">
                        <span>Pending</span>
                    </label>
                </div>
            </div>

            <!-- Penyesuaian Jam Final (Waktu Disetujui) -->
            <div id="waktu-disetujui-wrapper">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Jam Final Liputan yang Disetujui
                </label>
                <p class="text-[11px] text-slate-500 mb-2">Ubah jika jadwal disesuaikan dari permohonan awal (misal pemohon minta 10:00, disetujui 11:00).</p>
                <input type="time" name="waktu_disetujui" id="input_waktu_disetujui" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-sm transition font-semibold">
            </div>

            <!-- Catatan Admin Humas -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Catatan Tim Humas (Opsional)
                </label>
                <p class="text-[11px] text-slate-500 mb-2">Akan disertakan dalam pesan WhatsApp ke pemohon (nama tim liputan/alasan penolakan).</p>
                <textarea name="catatan_admin" id="input_catatan_admin" rows="3" placeholder="Contoh: Petugas fotografer: Bpk. Dani. Dimohon standby 15 menit sebelum acara." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-xs transition placeholder:text-slate-400"></textarea>
            </div>

            <!-- Action buttons -->
            <div class="pt-3 border-t border-slate-100 space-y-2">
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#0f2e5a] hover:bg-blue-900 text-white font-bold text-xs sm:text-sm shadow-md transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Keputusan & Kirim WhatsApp Otomatis
                </button>

                <!-- Fallback Button for Manual WA -->
                <a id="btn-manual-wa" href="#" target="_blank" class="w-full py-2.5 px-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-800 font-bold text-xs transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 fill-current text-emerald-600" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    Fallback: Kirim via WhatsApp Manual (wa.me)
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function openApprovalModal(orderId) {
        try {
            const res = await fetch(`/admin/orders/${orderId}`);
            const data = await res.json();
            const order = data.order;

            document.getElementById('modal-subheading').innerText = `Kode Booking: ${order.kode_booking}`;
            document.getElementById('detail-kegiatan').innerText = order.nama_kegiatan;
            document.getElementById('detail-pemohon').innerText = order.nama_pemohon;
            document.getElementById('detail-fraksi').innerText = order.fraksi;
            document.getElementById('detail-tanggal').innerText = data.tanggal_formatted;
            document.getElementById('detail-kategori').innerText = order.kategori;
            document.getElementById('detail-waktu-mulai').innerText = `${data.waktu_mulai_formatted} WIB`;
            document.getElementById('detail-jenis').innerText = order.jenis_publikasi;
            document.getElementById('detail-kontak').innerText = `📱 WA: ${order.no_whatsapp}`;

            const fileContainer = document.getElementById('detail-file-container');
            if (data.file_url) {
                fileContainer.innerHTML = `<a href="${data.file_url}" target="_blank" class="px-2.5 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded font-bold text-xs transition">📎 Download Surat/Flyer</a>`;
            } else {
                fileContainer.innerHTML = `<span class="text-slate-400 italic text-[11px]">Tidak ada lampiran dokumen</span>`;
            }

            // Radio status
            if (order.status === 'approved') {
                document.getElementById('status-approved').checked = true;
            } else if (order.status === 'rejected') {
                document.getElementById('status-rejected').checked = true;
            } else {
                document.getElementById('status-pending').checked = true;
            }

            document.getElementById('input_waktu_disetujui').value = data.waktu_disetujui_formatted;
            document.getElementById('input_catatan_admin').value = order.catatan_admin || '';

            // Form action
            document.getElementById('approvalForm').action = `/admin/orders/${order.id}/status`;

            // Manual link
            document.getElementById('btn-manual-wa').href = data.manual_wa_link;

            document.getElementById('approvalModal').classList.remove('hidden');
        } catch (e) {
            alert('Gagal mengambil data permohonan.');
        }
    }

    function closeApprovalModal() {
        document.getElementById('approvalModal').classList.add('hidden');
    }

    // Close on backdrop
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('approvalModal');
        if (e.target === modal) {
            closeApprovalModal();
        }
    });
</script>
@endpush
