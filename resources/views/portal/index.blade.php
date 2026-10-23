@extends('layouts.app')

@section('title', 'Pemesanan Peliputan Tim Humas DPRD Kota Tangerang')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-6">

    <!-- Top Hero Banner (Besar, Kontras & Jelas) -->
    <div class="bg-gradient-to-br from-[#0f2e5a] via-[#12396e] to-[#0a1d37] rounded-3xl shadow-xl overflow-hidden text-white border-2 border-blue-800 relative">
        <div class="p-6 sm:p-8 relative z-10">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-extrabold bg-amber-400 text-slate-950 uppercase tracking-wider shadow">
                        🏛 Layanan Resmi Humas & Protokol DPRD Kota Tangerang
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-snug">
                        Pemesanan Peliputan & Dokumentasi
                    </h1>
                    <p class="text-blue-100 text-sm sm:text-base max-w-2xl leading-relaxed font-medium">
                        Cek jadwal tim peliputan pada kalender atau daftar agenda. <span class="text-amber-300 font-bold">Klik tanggal mana saja</span> untuk memesan kegiatan.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto shrink-0">
                    <button type="button" onclick="switchTab('form')" class="inline-flex items-center justify-center gap-2.5 px-6 py-4 rounded-2xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold shadow-lg hover:shadow-xl transition transform active:scale-95 text-sm sm:text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        + Ajukan Liputan Baru
                    </button>
                    <button type="button" onclick="switchTab('calendar')" class="inline-flex items-center justify-center gap-2 px-5 py-4 rounded-2xl bg-white/10 hover:bg-white/20 border-2 border-white/20 text-white font-bold transition text-sm sm:text-base">
                        📅 Lihat Jadwal
                    </button>
                </div>
            </div>
        </div>

        <!-- Info strip -->
        <div class="bg-black/40 px-6 sm:px-8 py-3.5 text-xs sm:text-sm text-blue-100 border-t border-white/10 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                <span>Ketentuan: <strong>Maksimal order H-1</strong> (order hari H tetap diproses dengan memprioritaskan agenda resmi).</span>
            </div>
            <div class="font-bold text-white">
                Hari ini: <span class="px-2.5 py-0.5 rounded-lg bg-emerald-500 text-white font-extrabold">{{ $todayApprovedCount }} Kegiatan ACC</span>
            </div>
        </div>
    </div>

    <!-- Flash Success Notification (Setelah Mengirim Form) -->
    @if(session('success_order'))
    @php $newOrder = session('success_order'); @endphp
    <div id="success-alert" class="p-6 bg-emerald-50 dark:bg-emerald-950/60 border-2 border-emerald-500 rounded-3xl shadow-md text-slate-800 dark:text-slate-100">
        <div class="flex flex-col sm:flex-row items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-lg text-2xl font-bold">
                ✓
            </div>
            <div class="space-y-2 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-lg sm:text-xl font-extrabold text-emerald-950 dark:text-emerald-200">
                        Pengajuan Liputan Berhasil Dikirim!
                    </h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-400 text-slate-950">
                        Status: Menunggu Konfirmasi (Pending)
                    </span>
                </div>
                <p class="text-sm text-emerald-900 dark:text-emerald-300">
                    Kode Booking: <strong class="font-mono text-base px-3 py-1 bg-white dark:bg-slate-900 border border-emerald-400 rounded-xl text-emerald-800 dark:text-emerald-300 select-all font-bold">{{ $newOrder->kode_booking }}</strong>
                </p>
                <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                    Kegiatan: <strong>"{{ $newOrder->nama_kegiatan }}"</strong> pada <strong>{{ $newOrder->tanggal_kegiatan->isoFormat('dddd, D MMMM Y') }}</strong> jam <strong>{{ substr($newOrder->waktu_mulai, 0, 5) }} WIB</strong>.
                </p>

                <div class="pt-2 flex flex-wrap gap-3">
                    @if(session('wa_manual_link'))
                    <a href="{{ session('wa_manual_link') }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs sm:text-sm font-bold shadow-lg transition">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        Kirim Notifikasi via WhatsApp
                    </a>
                    @endif
                    <button type="button" onclick="switchTab('calendar')" class="inline-flex items-center gap-1.5 px-4 py-3 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-2xl text-xs sm:text-sm font-bold transition">
                        Lihat di Kalender →
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- MAIN SEGMENTED TAB SWITCHER (BESAR & JELAS) -->
    <div class="bg-slate-200 dark:bg-slate-800 p-1.5 rounded-3xl max-w-xl mx-auto flex items-center shadow-inner border border-slate-300 dark:border-slate-700">
        <button type="button" id="tab-btn-calendar" onclick="switchTab('calendar')" class="flex-1 py-3.5 px-4 rounded-2xl text-sm sm:text-base font-extrabold flex items-center justify-center gap-2 transition duration-200 bg-[#0f2e5a] text-white shadow-md">
            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Jadwal & Kalender</span>
        </button>
        <button type="button" id="tab-btn-form" onclick="switchTab('form')" class="flex-1 py-3.5 px-4 rounded-2xl text-sm sm:text-base font-extrabold flex items-center justify-center gap-2 transition duration-200 text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <span>Form Pengajuan</span>
        </button>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: CALENDAR VIEW (FULL WIDTH, SPACIOUS) -->
    <!-- ========================================== -->
    <div id="section-calendar" class="space-y-5">

        <!-- Banner Petunjuk Klik Jadwal Kosong (Sangat Ramah Lansia) -->
        <div class="bg-amber-100/90 dark:bg-amber-950/60 border-2 border-amber-400 dark:border-amber-700 rounded-3xl p-4 sm:p-5 flex items-center justify-between flex-wrap gap-3 text-amber-950 dark:text-amber-200 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-2xl sm:text-3xl">👉</span>
                <div>
                    <strong class="text-sm sm:text-base font-extrabold block">Ingin Memesan di Tanggal Tertentu?</strong>
                    <span class="text-xs sm:text-sm">Klik tanggal mana saja di kotak kalender (walau masih kosong), formulir akan langsung otomatis terisi tanggal tersebut.</span>
                </div>
            </div>
            <button type="button" onclick="switchTab('form')" class="px-5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-xs sm:text-sm shadow transition">
                + Isi Form Langsung
            </button>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-md border-2 border-slate-300 dark:border-slate-800 overflow-hidden">
            <!-- Subheader Kalender & Switch Tampilan (Grid vs Daftar Besar) -->
            <div class="p-4 sm:p-6 border-b-2 border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50 dark:bg-slate-800/60">
                <!-- Legend Status Warna (Besar & Jelas) -->
                <div class="flex items-center flex-wrap gap-3 sm:gap-4 text-xs sm:text-sm font-bold">
                    <span class="text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider block sm:inline">Keterangan:</span>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-300">
                        <span class="w-3 h-3 rounded-full bg-emerald-600"></span>
                        <span>🟢 HIJAU: Sudah ACC</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-300">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span>🟡 KUNING: Menunggu ACC</span>
                    </div>
                </div>

                <!-- Toggle Tampilan: Kalender Kotak vs Daftar Tulisan Besar -->
                <div class="flex items-center bg-slate-200 dark:bg-slate-800 p-1 rounded-2xl shrink-0">
                    <button type="button" id="btn-view-grid" onclick="setCalendarMode('grid')" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-extrabold transition bg-white dark:bg-slate-900 text-[#0f2e5a] dark:text-white shadow">
                        📅 Kotak Kalender
                    </button>
                    <button type="button" id="btn-view-list" onclick="setCalendarMode('list')" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-bold transition text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white">
                        📋 Daftar Agenda (Tulisan Besar)
                    </button>
                </div>
            </div>

            <!-- Area 1: FullCalendar Grid -->
            <div id="calendar-grid-wrapper" class="p-3 sm:p-6">
                <div id="calendar" class="min-h-[560px]"></div>
            </div>

            <!-- Area 2: Daftar Agenda Card View (Khusus Lansia / Mudah Dibaca) -->
            <div id="calendar-list-wrapper" class="hidden p-4 sm:p-8 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>📋</span> Daftar Agenda Peliputan Mendatang
                    </h3>
                    <span class="text-xs text-slate-500 font-semibold">{{ count($upcomingOrders) }} kegiatan terdaftar</span>
                </div>

                @forelse($upcomingOrders as $item)
                @php
                    $isApproved = $item->status === 'approved';
                    $waktuShow = $item->waktu_disetujui ? substr($item->waktu_disetujui, 0, 5) : substr($item->waktu_mulai, 0, 5);
                @endphp
                <div class="p-5 rounded-3xl border-2 {{ $isApproved ? 'border-emerald-300 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-amber-300 bg-amber-50/40 dark:bg-amber-950/20' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition hover:shadow-md">
                    <div class="flex items-start gap-4">
                        <!-- Date Badge Besar -->
                        <div class="w-16 h-16 rounded-2xl {{ $isApproved ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-slate-950' }} flex flex-col items-center justify-center shrink-0 font-extrabold shadow">
                            <span class="text-xl leading-none">{{ $item->tanggal_kegiatan->format('d') }}</span>
                            <span class="text-[11px] uppercase tracking-wider mt-0.5">{{ $item->tanggal_kegiatan->isoFormat('MMM') }}</span>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-extrabold {{ $isApproved ? 'bg-emerald-600 text-white' : 'bg-amber-400 text-slate-950' }}">
                                    {{ $isApproved ? '✓ SUDAH ACC' : '⏳ MENUNGGU ACC' }}
                                </span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                    {{ $item->tanggal_kegiatan->isoFormat('dddd, D MMMM Y') }} • <span class="text-blue-700 dark:text-blue-400 font-mono font-bold">{{ $waktuShow }} WIB</span>
                                </span>
                            </div>
                            <h4 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white leading-tight">
                                {{ $item->nama_kegiatan }}
                            </h4>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                                Pemohon: <strong>{{ $item->nama_pemohon }}</strong> | Kategori: <span class="font-semibold text-blue-700 dark:text-blue-400">{{ $item->kategori }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <button type="button" onclick="selectDateForBooking('{{ $item->tanggal_kegiatan->format('Y-m-d') }}')" class="px-4 py-2.5 rounded-xl bg-[#0f2e5a] hover:bg-blue-900 text-white font-bold text-xs transition shadow flex items-center gap-1.5">
                            + Pesan Tanggal Ini
                        </button>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center bg-slate-50 dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 space-y-2">
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-semibold">Belum ada agenda peliputan terdaftar untuk hari-hari mendatang.</p>
                    <button type="button" onclick="switchTab('form')" class="px-5 py-2.5 rounded-xl bg-amber-400 text-slate-950 font-bold text-xs shadow">
                        + Ajukan Liputan Pertama
                    </button>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Privacy Card -->
        <div class="bg-blue-50/80 dark:bg-blue-950/40 rounded-3xl p-5 border-2 border-blue-200 dark:border-blue-900 flex items-start gap-3.5 text-xs sm:text-sm text-blue-950 dark:text-blue-200">
            <span class="text-2xl shrink-0">🛡️</span>
            <div>
                <strong class="font-bold block text-sm sm:text-base mb-0.5">Keamanan & Perlindungan Privasi</strong>
                Nomor WhatsApp dan dokumen surat/flyer pemohon dirahasiakan dan hanya dapat diakses oleh tim Humas & Protokol DPRD demi kenyamanan Anggota Dewan.
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: FORM VIEW (CLEAN, BESAR, MUDAH) -->
    <!-- ========================================== -->
    <div id="section-form" class="hidden max-w-3xl mx-auto space-y-6">

        <!-- Banner Jika Memilih Tanggal dari Kalender -->
        <div id="picked-date-banner" class="hidden p-5 bg-emerald-50 dark:bg-emerald-950/60 border-2 border-emerald-500 rounded-3xl text-emerald-950 dark:text-emerald-100 flex items-center justify-between flex-wrap gap-3 shadow">
            <div class="flex items-center gap-3">
                <span class="text-3xl">📅</span>
                <div>
                    <span class="text-xs uppercase tracking-wider font-extrabold text-emerald-700 dark:text-emerald-300 block">Tanggal Terpilih Otomatis:</span>
                    <strong class="text-base sm:text-lg font-extrabold" id="picked-date-text"></strong>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('input_nama_kegiatan').focus()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition">
                Lanjut Isi Form ↓
            </button>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-md border-2 border-slate-300 dark:border-slate-800 overflow-hidden">
            <!-- Form Header -->
            <div class="p-6 sm:p-8 border-b-2 border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-[#0f2e5a] text-amber-400 flex items-center justify-center font-bold text-2xl shadow-md shrink-0">
                        ✍️
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Formulir Pengajuan Liputan</h2>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-0.5">Isi data permohonan liputan untuk kegiatan kedewanan Anda.</p>
                    </div>
                </div>
                <button type="button" onclick="switchTab('calendar')" class="text-xs sm:text-sm text-slate-600 hover:text-blue-600 dark:text-slate-400 font-bold flex items-center gap-1 transition shrink-0 pt-2">
                    ← Kembali ke Kalender
                </button>
            </div>

            <!-- Error Validation Alert -->
            @if ($errors->any())
            <div class="m-6 p-4 bg-rose-50 dark:bg-rose-950/40 border-2 border-rose-300 dark:border-rose-900 rounded-2xl text-rose-800 dark:text-rose-200 text-xs sm:text-sm space-y-1">
                <div class="font-extrabold flex items-center gap-1.5 text-sm">
                    ⚠️ Mohon lengkapi bagian yang belum sesuai:
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
                    <div class="flex items-center gap-2.5 pb-2 border-b-2 border-slate-200 dark:border-slate-800">
                        <span class="w-7 h-7 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 text-sm font-extrabold flex items-center justify-center">1</span>
                        <h3 class="text-sm sm:text-base font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">Detail Agenda Kegiatan</h3>
                    </div>

                    <!-- Nama Kegiatan -->
                    <div>
                        <label class="block text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">
                            Nama Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_kegiatan" id="input_nama_kegiatan" value="{{ old('nama_kegiatan') }}" required placeholder="Contoh: Rapat Dengar Pendapat / Kunjungan Kerja Lapangan" class="w-full px-4 py-3.5 rounded-2xl border-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/40 text-sm sm:text-base font-medium transition placeholder:text-slate-400">
                    </div>

                    <!-- Tanggal & Jam Mulai -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">
                                Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal_kegiatan" id="tanggal_kegiatan" value="{{ old('tanggal_kegiatan') }}" required min="{{ date('Y-m-d') }}" class="w-full px-4 py-3.5 rounded-2xl border-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-100 text-sm sm:text-base font-bold transition">

                            <!-- Preset Cepat Tanggal -->
                            <div class="flex items-center gap-2 mt-2 text-xs">
                                <span class="text-slate-400 font-semibold">Pilih cepat:</span>
                                <button type="button" onclick="setQuickDate(0)" class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 dark:hover:bg-blue-900 text-slate-700 dark:text-slate-300 font-bold transition">
                                    Hari Ini
                                </button>
                                <button type="button" onclick="setQuickDate(1)" class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 dark:hover:bg-blue-900 text-slate-700 dark:text-slate-300 font-bold transition">
                                    Besok
                                </button>
                                <button type="button" onclick="setQuickDate(2)" class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 dark:hover:bg-blue-900 text-slate-700 dark:text-slate-300 font-bold transition">
                                    Lusa
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">
                                Waktu Mulai (WIB) <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="waktu_mulai" id="waktu_mulai" value="{{ old('waktu_mulai', '09:00') }}" required class="w-full px-4 py-3.5 rounded-2xl border-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-100 text-sm sm:text-base font-bold transition">

                            <!-- Preset Cepat Jam -->
                            <div class="flex items-center gap-1.5 mt-2 text-xs">
                                <span class="text-slate-400 font-semibold">Preset jam:</span>
                                <button type="button" onclick="setQuickTime('09:00')" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 text-slate-700 dark:text-slate-300 font-bold transition">09:00</button>
                                <button type="button" onclick="setQuickTime('10:00')" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 text-slate-700 dark:text-slate-300 font-bold transition">10:00</button>
                                <button type="button" onclick="setQuickTime('13:30')" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 text-slate-700 dark:text-slate-300 font-bold transition">13:30</button>
                                <button type="button" onclick="setQuickTime('19:00')" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-100 text-slate-700 dark:text-slate-300 font-bold transition">19:00</button>
                            </div>
                        </div>
                    </div>

                    <!-- H-1 Warning Indicator -->
                    <div id="h1-warning" class="hidden p-4 bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-400 dark:border-amber-700 rounded-2xl text-amber-950 dark:text-amber-200 text-xs sm:text-sm flex items-start gap-3">
                        <span class="text-amber-600 text-xl font-bold">⚠️</span>
                        <div>
                            <strong class="font-bold">Peringatan Pemesanan Hari H:</strong> Standar permohonan peliputan adalah H-1. Permohonan hari H akan tetap diproses dengan memprioritaskan kegiatan resmi dewan.
                        </div>
                    </div>

                    <!-- Kategori (HANYA Pimpinan, Komisi I - IV; AKD Dihapus) -->
                    <div>
                        <label class="block text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">
                            Kategori Agenda <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($kategoriList as $kat)
                            <label class="flex items-center gap-3 p-3.5 rounded-2xl border-2 border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/80 dark:has-[:checked]:bg-blue-950/60 has-[:checked]:text-blue-950 dark:has-[:checked]:text-blue-200 text-slate-800 dark:text-slate-200 font-extrabold text-sm sm:text-base">
                                <input type="radio" name="kategori" value="{{ $kat }}" {{ old('kategori') === $kat ? 'checked' : ($loop->first && !old('kategori') ? 'checked' : '') }} class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                <span>{{ $kat }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Jenis Publikasi -->
                    <div>
                        <label class="block text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">
                            Jenis Publikasi yang Diinginkan <span class="text-rose-500">*</span>
                        </label>
                        <div class="space-y-2.5">
                            @foreach($jenisPublikasiList as $jp)
                            <label class="flex items-start gap-3 p-4 rounded-2xl border-2 border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/80 dark:has-[:checked]:bg-blue-950/60 has-[:checked]:text-blue-950 dark:has-[:checked]:text-blue-200 text-slate-800 dark:text-slate-200 font-bold text-xs sm:text-sm">
                                <input type="radio" name="jenis_publikasi" value="{{ $jp }}" {{ old('jenis_publikasi') === $jp ? 'checked' : ($loop->first && !old('jenis_publikasi') ? 'checked' : '') }} class="mt-0.5 w-4 h-4 text-blue-600 focus:ring-blue-500">
                                <span>{{ $jp }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- STEP 2: DATA PEMOHON (FRAKSI TELAH DIHAPUS) -->
                <div class="space-y-5 pt-4">
                    <div class="flex items-center gap-2.5 pb-2 border-b-2 border-slate-200 dark:border-slate-800">
                        <span class="w-7 h-7 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 text-sm font-extrabold flex items-center justify-center">2</span>
                        <h3 class="text-sm sm:text-base font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">Data Pemohon & Kontak</h3>
                    </div>

                    <!-- Nama Pemohon -->
                    <div>
                        <label class="block text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">
                            Nama Pemohon / Anggota Dewan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_pemohon" value="{{ old('nama_pemohon') }}" required placeholder="Contoh: H. Rusdi Alam / Staf Pendamping" class="w-full px-4 py-3.5 rounded-2xl border-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-100 text-sm sm:text-base font-medium transition placeholder:text-slate-400">
                    </div>

                    <!-- No WhatsApp Aktif -->
                    <div>
                        <label class="block text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">
                            Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                        </label>
                        <input type="tel" name="no_whatsapp" value="{{ old('no_whatsapp') }}" required placeholder="Contoh: 081234567890" class="w-full px-4 py-3.5 rounded-2xl border-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-100 text-sm sm:text-base font-mono transition placeholder:text-slate-400">
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-2">
                            📲 Konfirmasi ACC & jadwal tim liputan akan dikirimkan otomatis ke nomor WhatsApp ini.
                        </p>
                    </div>
                </div>

                <!-- STEP 3: DOKUMEN PENDUKUNG (OPSIONAL) -->
                <div class="space-y-4 pt-4">
                    <div class="flex items-center gap-2.5 pb-2 border-b-2 border-slate-200 dark:border-slate-800">
                        <span class="w-7 h-7 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 text-sm font-extrabold flex items-center justify-center">3</span>
                        <h3 class="text-sm sm:text-base font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">Dokumen Pendukung (Opsional)</h3>
                    </div>

                    <div class="p-6 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-3xl hover:border-blue-500 dark:hover:border-blue-500 transition text-center bg-slate-50/70 dark:bg-slate-800/40">
                        <input type="file" name="file_dokumen" id="file_dokumen" onchange="previewFileName(this)" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden">
                        <label for="file_dokumen" class="cursor-pointer flex flex-col items-center justify-center space-y-2">
                            <div class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl">
                                📎
                            </div>
                            <div class="text-sm sm:text-base font-extrabold text-slate-800 dark:text-slate-200">
                                Klik untuk upload Surat Tugas, Rundown Acara, atau Flyer
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Format: PDF, DOCX, JPG, PNG (Maksimal 10 MB)
                            </p>
                            <span id="selected-file-label" class="hidden inline-flex items-center gap-2 px-4 py-1.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold"></span>
                        </label>
                    </div>
                </div>

                <!-- TOMBOL SUBMIT (BESAR & JELAS) -->
                <div class="pt-4 space-y-3">
                    <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-[#0f2e5a] hover:bg-[#153f7c] text-white font-extrabold text-base sm:text-lg shadow-xl hover:shadow-2xl transition transform active:scale-[0.99] flex items-center justify-center gap-2">
                        <span>🚀 Kirim Pengajuan Liputan Sekarang</span>
                    </button>
                    <button type="button" onclick="switchTab('calendar')" class="w-full py-2.5 text-xs sm:text-sm font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition">
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
<div id="eventDetailModal" class="fixed inset-0 z-50 hidden bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl max-w-lg w-full border-2 border-slate-300 dark:border-slate-800 overflow-hidden transform transition-all">
        <div class="bg-[#0f2e5a] text-white px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                <h3 class="font-extrabold text-base sm:text-lg">Detail Jadwal Peliputan</h3>
            </div>
            <button type="button" onclick="closeModal()" class="text-blue-200 hover:text-white rounded-xl p-1 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs sm:text-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <span class="text-slate-500 dark:text-slate-400 font-bold">Status:</span>
                <span id="modal-status" class="px-3.5 py-1 rounded-full text-xs font-extrabold"></span>
            </div>

            <div>
                <span class="block text-slate-500 dark:text-slate-400 text-xs font-bold mb-1">Nama Kegiatan:</span>
                <p id="modal-kegiatan" class="text-slate-900 dark:text-white font-extrabold text-lg leading-snug"></p>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-1">
                <div class="p-4 bg-slate-100 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 uppercase font-extrabold">Pemohon</span>
                    <p id="modal-pemohon" class="font-bold text-slate-900 dark:text-white mt-1 text-sm"></p>
                </div>
                <div class="p-4 bg-slate-100 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 uppercase font-extrabold">Kategori</span>
                    <p id="modal-kategori" class="font-bold text-blue-700 dark:text-blue-400 mt-1 text-sm"></p>
                </div>
            </div>

            <div class="p-4 bg-slate-100 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2.5">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 dark:text-slate-400 font-semibold">Waktu Pelaksanaan:</span>
                    <span id="modal-waktu" class="font-extrabold text-slate-900 dark:text-white"></span>
                </div>
                <div class="flex justify-between items-start gap-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                    <span class="text-slate-500 dark:text-slate-400 font-semibold shrink-0">Jenis Publikasi:</span>
                    <span id="modal-jenis" class="font-bold text-slate-800 dark:text-slate-200 text-right"></span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-200 dark:border-slate-700">
                    <span class="text-slate-500 dark:text-slate-400 font-semibold">Kode Booking:</span>
                    <span id="modal-kode" class="font-mono font-bold text-slate-700 dark:text-slate-300"></span>
                </div>
            </div>

            <div class="text-center text-slate-400 text-xs">
                🛡️ Nomor kontak pemohon dilindungi demi privasi internal dewan.
            </div>
        </div>

        <div class="bg-slate-100 dark:bg-slate-800/90 px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
            <button type="button" id="modal-btn-book-date" class="px-5 py-3 bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold rounded-2xl text-xs sm:text-sm transition flex items-center gap-1.5 shadow">
                + Pesan di Tanggal Ini
            </button>
            <button type="button" onclick="closeModal()" class="px-4 py-3 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold rounded-2xl text-xs sm:text-sm transition">
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

            btnForm.className = "flex-1 py-3.5 px-4 rounded-2xl text-sm sm:text-base font-extrabold flex items-center justify-center gap-2 transition duration-200 bg-[#0f2e5a] text-white shadow-md";
            btnCal.className = "flex-1 py-3.5 px-4 rounded-2xl text-sm sm:text-base font-extrabold flex items-center justify-center gap-2 transition duration-200 text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white";

            formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            formSection.classList.add('hidden');
            calSection.classList.remove('hidden');

            btnCal.className = "flex-1 py-3.5 px-4 rounded-2xl text-sm sm:text-base font-extrabold flex items-center justify-center gap-2 transition duration-200 bg-[#0f2e5a] text-white shadow-md";
            btnForm.className = "flex-1 py-3.5 px-4 rounded-2xl text-sm sm:text-base font-extrabold flex items-center justify-center gap-2 transition duration-200 text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white";

            if (calendarInstance) {
                setTimeout(() => calendarInstance.render(), 60);
            }
        }
    }

    // Switch between Grid view and Big-Font List View
    function setCalendarMode(mode) {
        const gridWrapper = document.getElementById('calendar-grid-wrapper');
        const listWrapper = document.getElementById('calendar-list-wrapper');
        const btnGrid = document.getElementById('btn-view-grid');
        const btnList = document.getElementById('btn-view-list');

        if (mode === 'list') {
            gridWrapper.classList.add('hidden');
            listWrapper.classList.remove('hidden');
            btnList.className = "px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-extrabold transition bg-white dark:bg-slate-900 text-[#0f2e5a] dark:text-white shadow";
            btnGrid.className = "px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-bold transition text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white";
        } else {
            listWrapper.classList.add('hidden');
            gridWrapper.classList.remove('hidden');
            btnGrid.className = "px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-extrabold transition bg-white dark:bg-slate-900 text-[#0f2e5a] dark:text-white shadow";
            btnList.className = "px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-bold transition text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white";
            if (calendarInstance) {
                setTimeout(() => calendarInstance.render(), 50);
            }
        }
    }

    // Pick date from Calendar & auto-switch to form with friendly banner
    function selectDateForBooking(dateStr) {
        const input = document.getElementById('tanggal_kegiatan');
        if (input) {
            input.value = dateStr;
            input.dispatchEvent(new Event('change'));
        }

        // Format to Indonesian date string
        try {
            const parts = dateStr.split('-');
            const d = new Date(parts[0], parts[1] - 1, parts[2]);
            const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            const formatted = d.toLocaleDateString('id-ID', options);
            document.getElementById('picked-date-text').innerText = formatted;
            document.getElementById('picked-date-banner').classList.remove('hidden');
        } catch (e) {
            document.getElementById('picked-date-text').innerText = dateStr;
            document.getElementById('picked-date-banner').classList.remove('hidden');
        }

        switchTab('form');

        setTimeout(() => {
            const nameInput = document.getElementById('input_nama_kegiatan');
            if (nameInput) nameInput.focus();
        }, 200);
    }

    // Quick Date helper (0 = today, 1 = tomorrow, 2 = day after tomorrow)
    function setQuickDate(daysAhead) {
        const d = new Date();
        d.setDate(d.getDate() + daysAhead);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        const dateStr = `${yyyy}-${mm}-${dd}`;

        selectDateForBooking(dateStr);
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
        @if ($errors->any())
            switchTab('form');
        @elseif (request()->has('form') || request()->has('pesan'))
            switchTab('form');
        @endif

        const calendarEl = document.getElementById('calendar');
        calendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                list: 'Daftar'
            },
            events: '{{ route("portal.events") }}',

            // Click ANY empty date box -> directly auto-fill and switch to form!
            dateClick: function(info) {
                selectDateForBooking(info.dateStr);
            },

            // High-contrast custom event badge with large text & clear status
            eventContent: function(arg) {
                const props = arg.event.extendedProps;
                const isApproved = props.status === 'approved';
                const statusIcon = isApproved ? '✓' : '⏳';
                const bgClass = isApproved ? 'bg-emerald-600 text-white border-emerald-700' : 'bg-amber-400 text-slate-950 border-amber-500';
                const waktu = props.waktu ? props.waktu.split(' ')[0] : '';

                const el = document.createElement('div');
                el.className = `w-full px-2 py-1 rounded-lg border shadow-sm font-extrabold text-xs sm:text-sm flex items-center gap-1.5 overflow-hidden ${bgClass}`;
                el.innerHTML = `<span class="shrink-0 text-[11px] font-mono px-1 py-0.5 rounded bg-black/20">${statusIcon} ${waktu}</span> <span class="truncate">${arg.event.title.replace(/^\[.*?\]\s*/, '')}</span>`;
                return { domNodes: [el] };
            },

            // Event click -> modal detail
            eventClick: function(info) {
                const props = info.event.extendedProps;
                selectedEventDate = info.event.startStr ? info.event.startStr.split('T')[0] : null;

                document.getElementById('modal-kegiatan').innerText = props.nama_kegiatan || '-';
                document.getElementById('modal-pemohon').innerText = props.nama_pemohon || '-';
                document.getElementById('modal-kategori').innerText = props.kategori || '-';
                document.getElementById('modal-waktu').innerText = props.waktu || '-';
                document.getElementById('modal-jenis').innerText = props.jenis_publikasi || '-';
                document.getElementById('modal-kode').innerText = props.kode_booking || '-';

                const statusEl = document.getElementById('modal-status');
                statusEl.innerText = props.status_label || props.status;
                if (props.status === 'approved') {
                    statusEl.className = 'px-3.5 py-1 rounded-full text-xs font-extrabold bg-emerald-600 text-white shadow-sm';
                    statusEl.innerText = '✓ DISETUJUI (ACC)';
                } else {
                    statusEl.className = 'px-3.5 py-1 rounded-full text-xs font-extrabold bg-amber-400 text-slate-950 shadow-sm';
                    statusEl.innerText = '⏳ MENUNGGU ACC (PENDING)';
                }

                const bookDateBtn = document.getElementById('modal-btn-book-date');
                if (bookDateBtn) {
                    bookDateBtn.onclick = function() {
                        closeModal();
                        if (selectedEventDate) {
                            selectDateForBooking(selectedEventDate);
                        } else {
                            switchTab('form');
                        }
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

    window.addEventListener('click', function(e) {
        const modal = document.getElementById('eventDetailModal');
        if (e.target === modal) {
            closeModal();
        }
    });
</script>
@endpush
