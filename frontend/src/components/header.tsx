import Link from "next/link";
import { ThemeToggle } from "./theme-toggle";

export function Header() {
  return (
    <header className="sticky top-0 z-40 bg-navy-800 dark:bg-surface border-b border-white/10 dark:border-line">
      <div className="max-w-6xl mx-auto px-4 sm:px-6">
        <div className="flex items-center justify-between h-14 sm:h-16 gap-4">
          <Link href="/" className="flex items-center gap-2.5 min-w-0">
            <div className="w-9 h-9 rounded-lg bg-white/10 dark:bg-surface-2 flex items-center justify-center border border-white/15 dark:border-line shrink-0">
              <svg className="w-5 h-5 text-accent-400 dark:text-accent-500" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5zm0 4.18l6 3.33v4.49c0 3.7-2.55 7.17-6 8.12-3.45-.95-6-4.42-6-8.12v-4.49l6-3.33z" />
              </svg>
            </div>
            <div className="min-w-0">
              <div className="text-[10px] font-semibold tracking-[0.14em] uppercase text-accent-400 dark:text-accent-500 leading-none">
                DPRD Kota Tangerang
              </div>
              <div className="text-sm font-semibold leading-tight truncate text-white dark:text-ink">
                E-Liputan Humas
              </div>
            </div>
          </Link>

          <div className="flex items-center gap-2.5 sm:gap-3">
            <span className="hidden sm:block text-xs text-slate-400 dark:text-muted">
              Sub Bagian Humas &amp; Protokol
            </span>
            <ThemeToggle />
          </div>
        </div>
      </div>
    </header>
  );
}
