import Link from "next/link";

export function Header() {
  return (
    <header className="bg-navy-800 text-white sticky top-0 z-40">
      <div className="max-w-6xl mx-auto px-4 sm:px-6">
        <div className="flex items-center justify-between h-14 sm:h-16">
          <Link href="/" className="flex items-center gap-2.5 min-w-0">
            <div className="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center border border-white/15 shrink-0">
              <svg className="w-5 h-5 text-accent-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5zm0 4.18l6 3.33v4.49c0 3.7-2.55 7.17-6 8.12-3.45-.95-6-4.42-6-8.12v-4.49l6-3.33z" />
              </svg>
            </div>
            <div className="min-w-0">
              <div className="text-[10px] font-semibold tracking-wider uppercase text-accent-400 leading-none">
                DPRD Kota Tangerang
              </div>
              <div className="text-sm font-bold leading-tight truncate">E-Liputan Humas</div>
            </div>
          </Link>
          <nav className="flex items-center gap-1.5 sm:gap-2">
            <Link
              href="/"
              className="px-3 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/10 transition"
            >
              Portal
            </Link>
            <Link
              href="/login"
              className="px-3 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/10 transition"
            >
              Admin
            </Link>
          </nav>
        </div>
      </div>
    </header>
  );
}
