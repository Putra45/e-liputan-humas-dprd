"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { api, ensureCsrf } from "@/lib/api";

export default function LoginPage() {
  const router = useRouter();
  const [error, setError] = useState("");
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    ensureCsrf().catch(() => {});
  }, []);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError("");
    setSubmitting(true);
    const form = new FormData(e.currentTarget);
    try {
      await api.login(String(form.get("email") ?? ""), String(form.get("password") ?? ""));
      router.push("/admin/orders");
    } catch (err) {
      setError(err instanceof Error ? err.message : "Gagal masuk.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="max-w-5xl mx-auto px-4 sm:px-6 py-10 sm:py-16">
      <div className="grid lg:grid-cols-[1fr_1.05fr] rounded-2xl border border-line bg-surface overflow-hidden">
        <aside className="hidden lg:flex flex-col justify-between gap-10 bg-navy-800 dark:bg-surface-2 p-8">
          <div>
            <div className="w-10 h-10 rounded-lg bg-white/10 border border-white/15 grid place-items-center">
              <svg className="w-5 h-5 text-accent-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5zm0 4.18l6 3.33v4.49c0 3.7-2.55 7.17-6 8.12-3.45-.95-6-4.42-6-8.12v-4.49l6-3.33z" />
              </svg>
            </div>
            <h1 className="mt-5 text-xl font-bold text-white dark:text-ink">Portal verifikasi Humas</h1>
            <p className="mt-2 text-sm text-slate-300 dark:text-muted leading-relaxed">
              Tinjau permohonan liputan, atur personel, dan kirim konfirmasi WhatsApp ke pemohon.
            </p>
            <ul className="mt-6 space-y-3 text-sm text-slate-300 dark:text-ink-2">
              {[
                "Statistik permohonan harian",
                "Filter status, tanggal, dan pencarian",
                "Penyesuaian jam liputan final",
              ].map((item) => (
                <li key={item} className="flex items-start gap-2.5">
                  <svg
                    className="w-4 h-4 mt-0.5 text-accent-400 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth={2.2}
                  >
                    <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  {item}
                </li>
              ))}
            </ul>
          </div>
          <p className="text-xs text-slate-400 dark:text-muted">
            Akses terbatas untuk Tim Publikasi &amp; Protokol Sekretariat DPRD Kota Tangerang.
          </p>
        </aside>

        <div className="p-6 sm:p-8">
          <Link
            href="/"
            className="inline-flex items-center gap-1.5 text-xs font-medium text-muted hover:text-ink-2 transition-colors"
          >
            <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke portal
          </Link>

          <h2 className="mt-5 text-xl font-bold text-ink">Masuk ke akun admin</h2>
          <p className="mt-1 text-sm text-muted">Gunakan email dinas yang terdaftar.</p>

          <form onSubmit={handleSubmit} className="mt-6 space-y-4">
            {error && (
              <div
                role="alert"
                className="p-3 rounded-lg border border-rose-200 bg-rose-50 text-sm text-rose-800 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-200"
              >
                {error}
              </div>
            )}

            <div>
              <label htmlFor="email" className="block text-sm font-medium text-ink-2 mb-1.5">
                Email
              </label>
              <input
                id="email"
                name="email"
                type="email"
                required
                autoFocus
                autoComplete="username"
                placeholder="nama@dprd-tangerangkota.go.id"
                className="w-full px-3.5 py-2.5 rounded-lg bg-surface border border-line text-sm text-ink placeholder:text-muted focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition"
              />
            </div>

            <div>
              <label htmlFor="password" className="block text-sm font-medium text-ink-2 mb-1.5">
                Kata sandi
              </label>
              <input
                id="password"
                name="password"
                type="password"
                required
                autoComplete="current-password"
                className="w-full px-3.5 py-2.5 rounded-lg bg-surface border border-line text-sm text-ink focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition"
              />
            </div>

            <button
              type="submit"
              disabled={submitting}
              className="w-full py-2.5 rounded-lg bg-brand hover:brightness-110 disabled:opacity-60 text-white text-sm font-semibold transition"
            >
              {submitting ? "Memproses..." : "Masuk"}
            </button>
          </form>

          <p className="mt-6 text-xs text-muted leading-relaxed">
            Lupa kata sandi? Hubungi admin sistem di Sub Bagian Humas &amp; Protokol.
          </p>
        </div>
      </div>
    </div>
  );
}
