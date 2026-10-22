@extends('layouts.app')

@section('title', 'Login Admin Tim Humas DPRD Kota Tangerang')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">
        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-br from-[#0f2e5a] to-[#0a1d37] p-8 text-white text-center border-b-4 border-amber-500">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-white/10 flex items-center justify-center border border-white/20 mb-3 shadow-inner">
                    <svg class="w-8 h-8 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5zm0 4.18l6 3.33v4.49c0 3.7-2.55 7.17-6 8.12-3.45-.95-6-4.42-6-8.12v-4.49l6-3.33zM12 9a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-black tracking-tight">PORTAL ADMIN HUMAS</h2>
                <p class="text-xs text-blue-200 mt-1">Sekretariat DPRD Kota Tangerang</p>
            </div>

            <form action="{{ route('login.post') }}" method="POST" class="p-8 space-y-5">
                @csrf

                @if($errors->any())
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs">
                    {{ $errors->first() }}
                </div>
                @endif

                @if(session('success'))
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs">
                    {{ session('success') }}
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Alamat Email
                    </label>
                    <input type="email" name="email" value="{{ old('email', 'admin@dprd-tangerangkota.go.id') }}" required autofocus class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-sm transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kata Sandi
                    </label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-sm transition">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                        <input type="checkbox" name="remember" class="rounded text-blue-600 focus:ring-blue-500">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <div>
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#0f2e5a] hover:bg-[#1a447e] text-white font-bold text-sm shadow-md hover:shadow-lg transition">
                        Masuk ke Dashboard
                    </button>
                </div>

                <!-- Credential Hint -->
                <div class="p-3.5 bg-blue-50/70 border border-blue-200 rounded-xl text-[11px] text-blue-900 space-y-1">
                    <div class="font-bold flex items-center gap-1">
                        <span>🔑</span> Akun Bawaan (Default):
                    </div>
                    <div>Email: <strong class="font-mono">admin@dprd-tangerangkota.go.id</strong></div>
                    <div>Password: <strong class="font-mono">password123</strong></div>
                </div>
            </form>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('portal.index') }}" class="text-xs text-slate-500 hover:text-slate-800 transition inline-flex items-center gap-1">
                ← Kembali ke Portal Depan
            </a>
        </div>
    </div>
</div>
@endsection
