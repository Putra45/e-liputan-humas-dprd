@extends('layouts.app')

@section('title', 'Pemesanan Peliputan Tim Humas DPRD Kota Tangerang')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-6">

    <!-- Top Hero Banner (Compact & Clear) -->
    <div class="bg-gradient-to-br from-[#0f2e5a] via-[#12396e] to-[#0a1d37] rounded-3xl shadow-xl overflow-hidden text-white border border-blue-900/60 relative">
        <div class="p-6 sm:p-8 relative z-10">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-400 text-slate-950 uppercase tracking-wider">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Layanan Resmi Humas & Protokol
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-snug">
                        Pemesanan Peliputan Tim Humas DPRD Kota Tangerang
                    </h1>
                    <p class="text-blue-100/90 text-xs sm:text-sm max-w-2xl leading-relaxed">
                        Cek jadwal ketersediaan tim dokumentasi melalui kalender, atau ajukan permohonan peliputan baru untuk kegiatan kedewanan Anda dengan mudah.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-2.5 w-full md:w-auto shrink-0">
                    <button type="button" onclick="switchTab('form')" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold shadow-lg hover:shadow-xl transition transform active:scale-95 text-xs sm:text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        + Ajukan Liputan Baru
                    </button>
                    <button type="button" onclick="switchTab('calendar')" class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold transition text-xs sm:text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Lihat Jadwal
                    </button>
                </div>
            </div>
        </div>

        <!-- Info strip -->
        <div class="bg-black/30 px-6 sm:px-8 py-3 text-xs text-blue-200 border-t border-white/10 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                <span>Ketentuan: <strong>Maksimal order H-1</strong> (order hari H diprioritaskan untuk kegiatan resmi DPRD).</span>
            </div>
            <div class="font-medium text-white/90">
                Agenda hari ini: <span class="px-2 py-0.5 rounded-full bg-blue-500/30 text-amber-300 font-bold border border-blue-400/30">{{ $todayApprovedCount }} kegiatan</span>
            </div>
        </div>
    </div>

    <!-- Flash Success Notification (After Order Created) -->
    @if(session('success_order'))
    @php $newOrder = session('success_order'); @endphp
    <div id="success-alert" class="p-5 sm:p-6 bg-emerald-50 dark:bg-emerald-950/40 border-2 border-emerald-500 rounded-3xl shadow-sm text-slate-800 dark:text-slate-100 animate-fade-in">
        <div class="flex flex-col sm:flex-row items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="space-y-1.5 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-base sm:text-lg font-extrabold text-emerald-950 dark:text-emerald-200">
                        Pengajuan Liputan Berhasil Dikirim!
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        Status: Menunggu Konfirmasi (Pending)
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-emerald-900 dark:text-emerald-300">
                    Kode Booking: <strong class="font-mono text-sm px-2.5 py-0.5 bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 rounded-lg text-emerald-800 dark:text-emerald-300 select-all">{{ $newOrder->kode_booking }}</strong>
                </p>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Kegiatan <strong>"{{ $newOrder->nama_kegiatan }}"</strong> pada tanggal <strong>{{ $newOrder->tanggal_kegiatan->isoFormat('D MMMM Y') }}</strong> jam <strong>{{ substr($newOrder->waktu_mulai, 0, 5) }} WIB</strong> telah masuk ke antrean verifikasi tim Humas.
                </p>

                <div class="pt-3 flex flex-wrap gap-2.5">
                    @if(session('wa_manual_link'))
                    <a href="{{ session('wa_manual_link') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        Kirim Notifikasi via WhatsApp
                    </a>
                    @endif
                    <button type="button" onclick="switchTab('calendar')" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-xs font-bold transition">
                        Cek di Kalender →
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- MAIN SEGMENTED CONTROL / TAB SWITCHER -->
    <div class="bg-slate-200/80 dark:bg-slate-800/80 p-1.5 rounded-2xl max-w-xl mx-auto flex items-center shadow-inner">
        <button type="button" id="tab-btn-calendar" onclick="switchTab('calendar')" class="flex-1 py-3 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition duration-200 bg-[#0f2e5a] text-white shadow-md">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Jadwal & Kalender</span>
        </button>
        <button type="button" id="tab-btn-form" onclick="switchTab('form')" class="flex-1 py-3 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition duration-200 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <span>Form Pengajuan</span>
        </button>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: CALENDAR VIEW (FULL WIDTH, SPACIOUS) -->
    <!-- ========================================== -->
    <div id="section-calendar" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200/90 dark:border-slate-800 overflow-hidden">
            <!-- Calendar Subheader / Legend -->
            <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/60 dark:bg-slate-800/40">
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        Jadwal Peliputan Humas DPRD
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Klik tanggal atau klik jadwal kegiatan untuk melihat rincian agenda.
                    </p>
                </div>

                <!-- Legend & Quick Action -->
                <div class="flex items-center flex-wrap gap-4 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-[#0f2e5a]"></span>
                        <span class="text-slate-700 dark:text-slate-300 font-semibold">ACC (Disetujui)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-amber-500"></span>
                        <span class="text-slate-700 dark:text-slate-300 font-semibold">Pending</span>
                    </div>
                    <button type="button" onclick="switchTab('form')" class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition flex items-center gap-1 shadow-sm">
                        + Buat Pesanan
                    </button>
                </div>
            </div>

            <!-- Calendar Container -->
            <div class="p-3 sm:p-6">
                <!-- Hint for mobile users -->
                <div class="sm:hidden mb-3 p-2.5 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 rounded-xl text-[11px] text-blue-900 dark:text-blue-300 flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Gunakan tombol <strong>Daftar</strong> di kalender untuk melihat agenda harian dengan mudah di HP.</span>
                </div>

                <div id="calendar" class="min-h-[520px]"></div>
            </div>
        </div>

        <!-- Privacy & Helper Card -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-blue-50/70 dark:bg-blue-950/30 rounded-2xl p-4 border border-blue-100 dark:border-blue-900/50 flex items-start gap-3 text-xs text-blue-900 dark:text-blue-200">
                <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 flex items-center justify-center shrink-0 font-bold">
                    🛡️
                </div>
                <div>
                    <strong class="font-bold block text-sm mb-0.5">Keamanan & Privasi Kontak</strong>
                    Nomor WhatsApp pemohon dan dokumen kegiatan hanya dapat diakses oleh Admin Humas untuk menjaga kenyamanan privasi Anggota Dewan.
                </div>
            </div>

            <div class="bg-amber-50/70 dark:bg-amber-950/30 rounded-2xl p-4 border border-amber-200/80 dark:border-amber-900/50 flex items-start gap-3 text-xs text-amber-900 dark:text-amber-200">
                <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 flex items-center justify-center shrink-0 font-bold">
                    ⏱️
                </div>
                <div>
                    <strong class="font-bold block text-sm mb-0.5">Penyesuaian Waktu (WIB)</strong>
                    Jika jadwal kegiatan berdekatan dengan agenda dewan lainnya, Admin Humas akan berkoordinasi langsung via WhatsApp untuk penyesuaian jam tim.
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: FORM VIEW (CENTERED, CLEAN, EASY) -->
    <!-- ========================================== -->
    <div id="section-form" class="hidden max-w-3xl mx-auto space-y-6">

        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200/90 dark:border-slate-800 overflow-hidden">
            <!-- Form Header -->
            <div class="p-6 sm:p-8 border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 flex items-start justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-[#0f2e5a] text-amber-400 flex items-center justify-center font-bold text-xl shadow-md shrink-0">
                        ✍️
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white">Formulir Pengajuan Liputan</h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Lengkapi data kegiatan kedewanan Anda di bawah ini.</p>
                    </div>
                </div>
                <button type="button" onclick="switchTab('calendar')" class="text-xs text-slate-500 hover:text-blue-600 dark:text-slate-400 font-semibold flex items-center gap-1 transition shrink-0 pt-1">
                    ← Lihat Kalender
                </button>
            </div>

            <!-- Error Validation Alert -->
            @if ($errors->any())
            <div class="m-6 p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 rounded-2xl text-rose-800 dark:text-rose-200 text-xs space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-sm">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Mohon lengkapi bagian yang belum sesuai:
                </div>
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('portal.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-8" id="orderForm">
                @csrf

                <!-- STEP 1: INFORMASI KEGIATAN -->
                <div class="space-y-5">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center justify-center">1</span>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">Detail Agenda Kegiatan</h3>
                    </div>

                    <!-- Nama Kegiatan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Nama Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_kegiatan" id="input_nama_kegiatan" value="{{ old('nama_kegiatan') }}" required placeholder="Contoh: Rapat Dengar Pendapat Komisi I / Kunjungan Kerja Lapangan" class="w-full px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/40 text-sm sm:text-base transition placeholder:text-slate-400">
                    </div>

                    <!-- Tanggal & Jam Mulai -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal_kegiatan" id="tanggal_kegiatan" value="{{ old('tanggal_kegiatan') }}" required min="{{ date('Y-m-d') }}" class="w-full px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/40 text-sm transition">

                            <!-- Quick Date Preset Buttons -->
                            <div class="flex items-center gap-1.5 mt-2 text-[11px]">
                                <span class="text-slate-400">Pilih cepat:</span>
                                <button type="button" onclick="setQuickDate(0)" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 dark:hover:bg-blue-900/40 text-slate-700 dark:text-slate-300 font-semibold transition">
                                    Hari Ini
                                </button>
                                <button type="button" onclick="setQuickDate(1)" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 dark:hover:bg-blue-900/40 text-slate-700 dark:text-slate-300 font-semibold transition">
                                    Besok
                                </button>
                                <button type="button" onclick="setQuickDate(2)" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 dark:hover:bg-blue-900/40 text-slate-700 dark:text-slate-300 font-semibold transition">
                                    Lusa
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Waktu Mulai (WIB) <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="waktu_mulai" id="waktu_mulai" value="{{ old('waktu_mulai', '09:00') }}" required class="w-full px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/40 text-sm transition">

                            <!-- Quick Time Presets -->
                            <div class="flex items-center gap-1.5 mt-2 text-[11px]">
                                <span class="text-slate-400">Preset:</span>
                                <button type="button" onclick="setQuickTime('09:00')" class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 text-slate-700 dark:text-slate-300 font-semibold transition">09:00</button>
                                <button type="button" onclick="setQuickTime('10:00')" class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 text-slate-700 dark:text-slate-300 font-semibold transition">10:00</button>
                                <button type="button" onclick="setQuickTime('13:30')" class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 text-slate-700 dark:text-slate-300 font-semibold transition">13:30</button>
                                <button type="button" onclick="setQuickTime('19:00')" class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 text-slate-700 dark:text-slate-300 font-semibold transition">19:00</button>
                            </div>
                        </div>
                    </div>

                    <!-- H-1 Warning Indicator -->
                    <div id="h1-warning" class="hidden p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 rounded-2xl text-amber-900 dark:text-amber-200 text-xs flex items-start gap-3">
                        <span class="text-amber-600 text-base">⚠️</span>
                        <div>
                            <strong class="font-bold">Peringatan Pemesanan Hari H:</strong> Standar permohonan tim peliputan adalah maksimal H-1. Permohonan hari H akan tetap diproses dengan memprioritaskan agenda resmi dewan.
                        </div>
                    </div>

                    <!-- Kategori (Clean Interactive Cards) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Kategori Agenda <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            @foreach($kategoriList as $kat)
                            <label class="flex items-center gap-2.5 p-3 rounded-2xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/70 dark:has-[:checked]:bg-blue-950/50 has-[:checked]:text-blue-950 dark:has-[:checked]:text-blue-200 text-slate-800 dark:text-slate-200 font-bold text-xs sm:text-sm">
                                <input type="radio" name="kategori" value="{{ $kat }}" {{ old('kategori') === $kat ? 'checked' : ($loop->first && !old('kategori') ? 'checked' : '') }} class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                <span>{{ $kat }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Jenis Publikasi (Clean Interactive Cards) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Jenis Publikasi yang Diinginkan <span class="text-rose-500">*</span>
                        </label>
                        <div class="space-y-2">
                            @foreach($jenisPublikasiList as $jp)
                            <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/70 dark:has-[:checked]:bg-blue-950/50 has-[:checked]:text-blue-950 dark:has-[:checked]:text-blue-200 text-slate-800 dark:text-slate-200 font-semibold text-xs sm:text-sm">
                                <input type="radio" name="jenis_publikasi" value="{{ $jp }}" {{ old('jenis_publikasi') === $jp ? 'checked' : ($loop->first && !old('jenis_publikasi') ? 'checked' : '') }} class="mt-0.5 w-4 h-4 text-blue-600 focus:ring-blue-500">
                                <span>{{ $jp }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- STEP 2: IDENTITAS PEMOHON -->
                <div class="space-y-5 pt-4">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center justify-center">2</span>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">Data Pemohon & Kontak</h3>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Nama Pemohon / Anggota Dewan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_pemohon" value="{{ old('nama_pemohon') }}" required placeholder="Contoh: H. Rusdi Alam / Staf Pendamping Fraksi" class="w-full px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-100 text-sm transition placeholder:text-slate-400">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Fraksi <span class="text-rose-500">*</span>
                            </label>
                            <select name="fraksi" required class="w-full px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-100 text-sm transition">
                                <option value="">-- Pilih Fraksi --</option>
                                @foreach($fraksiList as $f)
                                    <option value="{{ $f }}" {{ old('fraksi') === $f ? 'selected' : '' }}>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" name="no_whatsapp" value="{{ old('no_whatsapp') }}" required placeholder="Contoh: 081234567890" class="w-full px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-100 text-sm transition placeholder:text-slate-400">
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">
                                📲 Konfirmasi ACC & penyesuaian jam tim Humas akan dikirim ke nomor ini.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: DOKUMEN PENDUKUNG (OPSIONAL) -->
                <div class="space-y-4 pt-4">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center justify-center">3</span>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">Dokumen Pendukung (Opsional)</h3>
                    </div>

                    <div class="p-5 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-3xl hover:border-blue-500 dark:hover:border-blue-500 transition text-center bg-slate-50/50 dark:bg-slate-800/30">
                        <input type="file" name="file_dokumen" id="file_dokumen" onchange="previewFileName(this)" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden">
                        <label for="file_dokumen" class="cursor-pointer flex flex-col items-center justify-center space-y-2">
                            <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl">
                                📎
                            </div>
                            <div class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200">
                                Klik untuk upload Surat Tugas, Rundown Acara, atau Flyer
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Format: PDF, DOCX, JPG, atau PNG (Maksimal 10 MB)
                            </p>
                            <span id="selected-file-label" class="hidden inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold"></span>
                        </label>
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="pt-4 space-y-3">
                    <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-[#0f2e5a] hover:bg-[#153f7c] text-white font-extrabold text-sm sm:text-base shadow-xl hover:shadow-2xl transition transform active:scale-[0.99] flex items-center justify-center gap-2">
                        <span>🚀 Kirim Pengajuan Liputan</span>
                    </button>
                    <button type="button" onclick="switchTab('calendar')" class="w-full py-2.5 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition">
                        Batal & Kembali ke Kalender
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- ======================================================== -->
<!-- MODAL POPUP: DETAIL JADWAL KEGIATAN (FULLCALENDAR CLICK) -->
<!-- ======================================================== -->
<div id="eventDetailModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl max-w-lg w-full border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all animate-fade-in">
        <div class="bg-[#0f2e5a] text-white px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                <h3 class="font-extrabold text-base">Detail Jadwal Peliputan</h3>
            </div>
            <button type="button" onclick="closeModal()" class="text-blue-200 hover:text-white rounded-xl p-1 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs sm:text-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-500 dark:text-slate-400 font-medium">Status Permohonan:</span>
                <span id="modal-status" class="px-3 py-1 rounded-full text-xs font-bold"></span>
            </div>

            <div>
                <span class="block text-slate-500 dark:text-slate-400 text-xs font-semibold mb-1">Nama Kegiatan:</span>
                <p id="modal-kegiatan" class="text-slate-900 dark:text-white font-extrabold text-base leading-snug"></p>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-1">
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-700">
                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 uppercase font-bold">Pemohon / Fraksi</span>
                    <p id="modal-pemohon" class="font-bold text-slate-900 dark:text-white mt-1"></p>
                </div>
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-700">
                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 uppercase font-bold">Kategori</span>
                    <p id="modal-kategori" class="font-bold text-blue-700 dark:text-blue-400 mt-1"></p>
                </div>
            </div>

            <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-700 space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 dark:text-slate-400">Waktu Mulai:</span>
                    <span id="modal-waktu" class="font-extrabold text-slate-900 dark:text-white"></span>
                </div>
                <div class="flex justify-between items-start gap-2 pt-1 border-t border-slate-200/60 dark:border-slate-700">
                    <span class="text-slate-500 dark:text-slate-400 shrink-0">Publikasi:</span>
                    <span id="modal-jenis" class="font-semibold text-slate-800 dark:text-slate-200 text-right"></span>
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-slate-200/60 dark:border-slate-700">
                    <span class="text-slate-500 dark:text-slate-400">Kode Booking:</span>
                    <span id="modal-kode" class="font-mono font-bold text-slate-700 dark:text-slate-300"></span>
                </div>
            </div>

            <div class="text-center text-slate-400 text-[11px]">
                🛡️ Nomor telepon pemohon dirahasiakan untuk perlindungan privasi.
            </div>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800/80 px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
            <button type="button" id="modal-btn-book-date" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
                + Pesan di Tanggal Ini
            </button>
            <button type="button" onclick="closeModal()" class="px-4 py-2.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold rounded-xl text-xs transition">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let calendarInstance = null;
    let selectedEventDate = null;

    // Switch between Calendar and Form view
    function switchTab(tab) {
        const calSection = document.getElementById('section-calendar');
        const formSection = document.getElementById('section-form');
        const btnCal = document.getElementById('tab-btn-calendar');
        const btnForm = document.getElementById('tab-btn-form');

        if (tab === 'form') {
            calSection.classList.add('hidden');
            formSection.classList.remove('hidden');

            btnForm.className = "flex-1 py-3 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition duration-200 bg-[#0f2e5a] text-white shadow-md";
            btnCal.className = "flex-1 py-3 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition duration-200 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white";

            // Focus to form on top
            formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            formSection.classList.add('hidden');
            calSection.classList.remove('hidden');

            btnCal.className = "flex-1 py-3 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition duration-200 bg-[#0f2e5a] text-white shadow-md";
            btnForm.className = "flex-1 py-3 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition duration-200 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white";

            // Re-render calendar so it resizes properly
            if (calendarInstance) {
                setTimeout(() => calendarInstance.render(), 50);
            }
        }
    }

    // Quick Date helper (0 = today, 1 = tomorrow, 2 = day after tomorrow)
    function setQuickDate(daysAhead) {
        const d = new Date();
        d.setDate(d.getDate() + daysAhead);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        const dateStr = `${yyyy}-${mm}-${dd}`;

        const input = document.getElementById('tanggal_kegiatan');
        if (input) {
            input.value = dateStr;
            input.dispatchEvent(new Event('change'));
        }
    }

    // Quick Time helper
    function setQuickTime(timeStr) {
        const input = document.getElementById('waktu_mulai');
        if (input) {
            input.value = timeStr;
        }
    }

    // File Preview Helper
    function previewFileName(fileInput) {
        const label = document.getElementById('selected-file-label');
        if (fileInput.files && fileInput.files.length > 0) {
            label.textContent = `✓ ${fileInput.files[0].name}`;
            label.classList.remove('hidden');
        } else {
            label.classList.add('hidden');
        }
    }

    // Modal helpers
    function closeModal() {
        document.getElementById('eventDetailModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Automatically open form tab if there are errors or URL has #form
        @if ($errors->any())
            switchTab('form');
        @elseif (request()->has('form') || request()->has('pesan'))
            switchTab('form');
        @endif

        // Responsive initial view: on small screens (mobile/HP), listMonth is much easier to read
        const isMobile = window.innerWidth < 640;

        const calendarEl = document.getElementById('calendar');
        calendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: isMobile ? 'listMonth' : 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                list: 'Daftar Agenda'
            },
            events: '{{ route("portal.events") }}',
            dateClick: function(info) {
                // When user clicks a date cell on the calendar: prefill date and switch to form!
                const input = document.getElementById('tanggal_kegiatan');
                if (input) {
                    input.value = info.dateStr;
                    input.dispatchEvent(new Event('change'));
                }
                switchTab('form');
            },
            eventClick: function(info) {
                const props = info.event.extendedProps;
                selectedEventDate = info.event.startStr ? info.event.startStr.split('T')[0] : null;

                document.getElementById('modal-kegiatan').innerText = props.nama_kegiatan || '-';
                document.getElementById('modal-pemohon').innerText = `${props.nama_pemohon} (${props.fraksi})`;
                document.getElementById('modal-kategori').innerText = props.kategori || '-';
                document.getElementById('modal-waktu').innerText = props.waktu || '-';
                document.getElementById('modal-jenis').innerText = props.jenis_publikasi || '-';
                document.getElementById('modal-kode').innerText = props.kode_booking || '-';

                const statusEl = document.getElementById('modal-status');
                statusEl.innerText = props.status_label || props.status;
                if (props.status === 'approved') {
                    statusEl.className = 'px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
                } else {
                    statusEl.className = 'px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300';
                }

                // Setup "Pesan di Tanggal Ini" button in modal
                const bookDateBtn = document.getElementById('modal-btn-book-date');
                if (bookDateBtn) {
                    bookDateBtn.onclick = function() {
                        closeModal();
                        if (selectedEventDate) {
                            const input = document.getElementById('tanggal_kegiatan');
                            if (input) {
                                input.value = selectedEventDate;
                                input.dispatchEvent(new Event('change'));
                            }
                        }
                        switchTab('form');
                    };
                }

                document.getElementById('eventDetailModal').classList.remove('hidden');
            }
        });

        calendarInstance.render();

        // Check H-1 input date warning
        const tanggalInput = document.getElementById('tanggal_kegiatan');
        const warningBox = document.getElementById('h1-warning');
        if (tanggalInput && warningBox) {
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

    // Close on backdrop click
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('eventDetailModal');
        if (e.target === modal) {
            closeModal();
        }
    });
</script>
@endpush