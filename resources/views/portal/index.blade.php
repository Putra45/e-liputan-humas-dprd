@extends('layouts.app')

@section('title', 'Pemesanan Peliputan Tim Humas DPRD Kota Tangerang')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Top Rule / Notification Banner (Matching Google Form Header) -->
    <div class="mb-8 bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-2xl shadow-lg overflow-hidden border border-blue-800 text-white">
        <div class="p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-amber-400 text-slate-950 uppercase tracking-wide">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ketentuan Operasional
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Pemesanan Peliputan Tim Humas DPRD Kota Tangerang
                </h1>
                <p class="text-blue-100 text-sm sm:text-base max-w-3xl leading-relaxed">
                    Pemesanan tim publikasi <strong class="text-amber-300 font-semibold">maksimal order H-1</strong>. Untuk order di hari H yang bersifat kegiatan resmi DPRD akan tetap diprioritaskan.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto shrink-0">
                <a href="#form-section" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold shadow-md hover:shadow-lg transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Pesanan Baru
                </a>
                <a href="#calendar-section" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-medium transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Cek Kalender
                </a>
            </div>
        </div>
        <div class="bg-black/20 px-6 sm:px-8 py-3 text-xs text-blue-200 border-t border-white/10 flex flex-wrap items-center justify-between gap-2">
            <div>💡 Transparansi jadwal publikasi internal untuk menghindari bentrok agenda antar fraksi & komisi.</div>
            <div>Jadwal liputan hari ini: <span class="font-bold text-white">{{ $todayApprovedCount }} kegiatan</span></div>
        </div>
    </div>

    <!-- Flash Success Notification (After Form Submission) -->
    @if(session('success_order'))
    @php $newOrder = session('success_order'); @endphp
    <div id="success-alert" class="mb-8 p-6 bg-emerald-50 border-2 border-emerald-500 rounded-2xl shadow-sm text-slate-800">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="space-y-1 flex-1">
                <h3 class="text-lg font-bold text-emerald-950">Pengajuan Liputan Berhasil Dikirim!</h3>
                <p class="text-sm text-emerald-800">
                    Kode Booking: <span class="font-mono font-bold text-base px-2 py-0.5 bg-white border border-emerald-300 rounded text-emerald-900">{{ $newOrder->kode_booking }}</span>
                </p>
                <p class="text-xs text-slate-600 mt-2">
                    Pengajuan untuk kegiatan <strong>"{{ $newOrder->nama_kegiatan }}"</strong> telah masuk ke antrean tim Humas (Status: <span class="font-semibold text-amber-700">Pending</span>). Konfirmasi ACC beserta penyesuaian jam akan dikirim ke nomor WhatsApp <strong>{{ $newOrder->no_whatsapp }}</strong>.
                </p>
                @if(session('wa_manual_link'))
                <div class="pt-3 flex gap-3">
                    <a href="{{ session('wa_manual_link') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow transition">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        Kirim Notif Mandiri ke WhatsApp
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Main Grid: Left = Calendar, Right = Form -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Left Column: Interactive Calendar (7 Cols on LG) -->
        <div id="calendar-section" class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-600 inline-block"></span>
                            Kalender Agenda Liputan Humas
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Klik jadwal pada tanggal untuk melihat rincian kegiatan.</p>
                    </div>
                    <!-- Legend -->
                    <div class="flex items-center gap-3 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-[#0f2e5a]"></span>
                            <span class="text-slate-600 font-medium">Disetujui (ACC)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-amber-500"></span>
                            <span class="text-slate-600 font-medium">Pending</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div id="calendar" class="min-h-[560px]"></div>
                </div>
            </div>

            <!-- Transparency Info Card -->
            <div class="bg-blue-50/60 rounded-xl p-4 border border-blue-100 flex items-start gap-3 text-xs text-blue-900">
                <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <strong class="font-semibold">Privasi Kontak:</strong> Nomor WhatsApp pemohon dan lampiran surat/flyer hanya dapat diakses oleh Admin Humas demi kenyamanan dan perlindungan data pribadi dewan.
                </div>
            </div>
        </div>

        <!-- Right Column: Booking Form (5 Cols on LG) -->
        <div id="form-section" class="lg:col-span-5">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-24">
                <div class="p-5 border-b border-slate-100 bg-slate-50/80">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                            📝
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Form Pengajuan Liputan</h2>
                            <p class="text-xs text-slate-500">Isi data lengkap sesuai format permohonan resmi.</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('portal.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5" id="orderForm">
                    @csrf

                    @if ($errors->any())
                    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Terdapat kesalahan pengisian:
                        </div>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Nama Kegiatan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" required placeholder="Contoh: Rapat Dengar Pendapat / Kunjungan Kerja" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-sm transition placeholder:text-slate-400">
                    </div>

                    <!-- Kategori (Radio) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Kategori <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            @foreach($kategoriList as $kat)
                            <label class="flex items-center gap-2 p-2.5 border border-slate-200 rounded-xl hover:bg-slate-50 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50 has-[:checked]:text-blue-900 font-medium">
                                <input type="radio" name="kategori" value="{{ $kat }}" {{ old('kategori') === $kat ? 'checked' : ($loop->first && !old('kategori') ? 'checked' : '') }} class="text-blue-600 focus:ring-blue-500">
                                <span>{{ $kat }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tanggal & Jam -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tanggal Kegiatan <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal_kegiatan" id="tanggal_kegiatan" value="{{ old('tanggal_kegiatan') }}" required min="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-sm transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Waktu Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai', '09:00') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-sm transition">
                        </div>
                    </div>

                    <!-- H-1 Warning Indicator -->
                    <div id="h1-warning" class="hidden p-3 bg-amber-50 border border-amber-300 rounded-xl text-amber-900 text-xs flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <strong>Peringatan Pemesanan Hari H:</strong> Standar order adalah minimal H-1. Permohonan di hari H tetap kami teruskan dan akan diprioritaskan untuk kegiatan resmi DPRD.
                        </div>
                    </div>

                    <!-- Jenis Publikasi (Radio) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Jenis Publikasi <span class="text-rose-500">*</span>
                        </label>
                        <div class="space-y-2 text-xs">
                            @foreach($jenisPublikasiList as $jp)
                            <label class="flex items-start gap-2.5 p-2.5 border border-slate-200 rounded-xl hover:bg-slate-50 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50 has-[:checked]:text-blue-900 font-medium">
                                <input type="radio" name="jenis_publikasi" value="{{ $jp }}" {{ old('jenis_publikasi') === $jp ? 'checked' : ($loop->first && !old('jenis_publikasi') ? 'checked' : '') }} class="mt-0.5 text-blue-600 focus:ring-blue-500">
                                <span>{{ $jp }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Identitas Pemohon -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                        <div class="text-xs font-bold text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Data Pemohon & Kontak
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">
                                Nama Pemohon / Anggota Dewan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama_pemohon" value="{{ old('nama_pemohon') }}" required placeholder="Contoh: H. Rusdi Alam / Staf Pendamping" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-xs transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">
                                    Fraksi <span class="text-rose-500">*</span>
                                </label>
                                <select name="fraksi" required class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-xs transition bg-white">
                                    <option value="">-- Pilih Fraksi --</option>
                                    @foreach($fraksiList as $f)
                                        <option value="{{ $f }}" {{ old('fraksi') === $f ? 'selected' : '' }}>{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">
                                    No. WhatsApp Aktif <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="no_whatsapp" value="{{ old('no_whatsapp') }}" required placeholder="0812xxxxxxxx" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-xs transition">
                            </div>
                        </div>
                    </div>

                    <!-- Dokumen Kegiatan (Surat / Flyer) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Dokumen Kegiatan (Surat / Flyer)
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Upload surat tugas, susunan acara, atau flyer (PDF, JPG, PNG, DOCX - Maks 10 MB).</p>
                        <input type="file" name="file_dokumen" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-slate-200 rounded-xl p-1 bg-white">
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#0f2e5a] hover:bg-[#1a447e] text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            Kirim Formulir Pemesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Modal Pop-up: Detail Event Jadwal (FullCalendar Click) -->
<div id="eventDetailModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full border border-slate-200 overflow-hidden transform transition-all">
        <div class="bg-[#0f2e5a] text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                <h3 class="font-bold text-base">Detail Jadwal Peliputan</h3>
            </div>
            <button type="button" onclick="closeModal()" class="text-slate-300 hover:text-white rounded-lg p-1 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs sm:text-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <span class="text-slate-500 font-medium">Status Pengajuan</span>
                <span id="modal-status" class="px-2.5 py-0.5 rounded-full text-xs font-semibold"></span>
            </div>

            <div>
                <span class="block text-slate-500 text-xs font-medium mb-0.5">Nama Kegiatan</span>
                <p id="modal-kegiatan" class="text-slate-900 font-bold text-base leading-snug"></p>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="block text-[11px] text-slate-500 uppercase font-bold">Pemohon (Fraksi)</span>
                    <p id="modal-pemohon" class="font-semibold text-slate-900 mt-0.5"></p>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="block text-[11px] text-slate-500 uppercase font-bold">Kategori</span>
                    <p id="modal-kategori" class="font-semibold text-blue-700 mt-0.5"></p>
                </div>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-500">Waktu Liputan:</span>
                    <span id="modal-waktu" class="font-bold text-slate-900"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Jenis Publikasi:</span>
                    <span id="modal-jenis" class="font-semibold text-slate-800 text-right max-w-[240px]"></span>
                </div>
                <div class="flex justify-between pt-1 border-t border-slate-200/60">
                    <span class="text-slate-500">Kode Booking:</span>
                    <span id="modal-kode" class="font-mono font-bold text-slate-700"></span>
                </div>
            </div>

            <div class="pt-2 text-center text-slate-400 text-[11px]">
                🛡️ Rincian kontak person disembunyikan demi perlindungan privasi internal.
            </div>
        </div>

        <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex justify-end">
            <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-xl text-xs transition">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // FullCalendar Init
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                list: 'Daftar'
            },
            events: '{{ route("portal.events") }}',
            eventClick: function(info) {
                const props = info.event.extendedProps;
                document.getElementById('modal-kegiatan').innerText = props.nama_kegiatan || '-';
                document.getElementById('modal-pemohon').innerText = `${props.nama_pemohon} (${props.fraksi})`;
                document.getElementById('modal-kategori').innerText = props.kategori || '-';
                document.getElementById('modal-waktu').innerText = props.waktu || '-';
                document.getElementById('modal-jenis').innerText = props.jenis_publikasi || '-';
                document.getElementById('modal-kode').innerText = props.kode_booking || '-';

                const statusEl = document.getElementById('modal-status');
                statusEl.innerText = props.status_label || props.status;
                if (props.status === 'approved') {
                    statusEl.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300';
                } else {
                    statusEl.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300';
                }

                document.getElementById('eventDetailModal').classList.remove('hidden');
            }
        });
        calendar.render();

        // Check H-1 input date warning
        const tanggalInput = document.getElementById('tanggal_kegiatan');
        const warningBox = document.getElementById('h1-warning');
        if (tanggalInput) {
            tanggalInput.addEventListener('change', function() {
                const today = new Date().toISOString().split('T')[0];
                if (this.value === today) {
                    warningBox.classList.remove('hidden');
                } else {
                    warningBox.classList.add('hidden');
                }
            });
        }
    });

    function closeModal() {
        document.getElementById('eventDetailModal').classList.add('hidden');
    }

    // Close on backdrop click
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('eventDetailModal');
        if (e.target === modal) {
            closeModal();
        }
    });
</script>
@endpush
