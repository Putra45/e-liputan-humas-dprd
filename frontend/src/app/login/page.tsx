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
    <div className="min-h-[75vh] flex items-center justify-center px-4 py-12">
      <div className="max-w-sm w-full">
        <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden">
          <div className="bg-navy-800 p-6 text-white text-center">
            <div className="w-11 h-11 mx-auto rounded-lg bg-white/10 flex items-center justify-center border border-white/15 mb-3">
              <svg className="w-6 h-6 text-accent-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5zm0 4.18l6 3.33v4.49c0 3.7-2.55 7.17-6 8.12-3.45-.95-6-4.42-6-8.12v-4.49l6-3.33zM12 9a2 2 0 100 4 2 2 0 000-4z" />
              </svg>
            </div>
            <h1 className="text-base font-bold">Portal Admin Humas</h1>
            <p className="text-xs text-slate-300 mt-0.5">Sekretariat DPRD Kota Tangerang</p>
          </div>

          <form onSubmit={handleSubmit} className="p-6 space-y-4">
            {error && (
              <div role="alert" className="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                {error}
              </div>
            )}

            <div>
              <label htmlFor="email" className="block text-sm font-medium text-slate-700 mb-1">
                Email
              </label>
              <input
                id="email"
                name="email"
                type="email"
                required
                autoFocus
                autoComplete="username"
                placeholder="email@dprd-tangerangkota.go.id"
                className="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:border-navy-600 focus:ring-2 focus:ring-navy-600/15 outline-none transition"
              />
            </div>

            <div>
              <label htmlFor="password" className="block text-sm font-medium text-slate-700 mb-1">
                Kata sandi
              </label>
              <input
                id="password"
                name="password"
                type="password"
                required
                autoComplete="current-password"
                className="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:border-navy-600 focus:ring-2 focus:ring-navy-600/15 outline-none transition"
              />
            </div>

            <button
              type="submit"
              disabled={submitting}
              className="w-full py-2.5 rounded-lg bg-navy-800 hover:bg-navy-700 disabled:opacity-60 text-white font-semibold text-sm transition-colors"
            >
              {submitting ? "Memproses..." : "Masuk"}
            </button>
          </form>
        </div>

        <div className="text-center mt-5">
          <Link href="/" className="text-sm text-slate-500 hover:text-slate-700 transition-colors">
            Kembali ke portal
          </Link>
        </div>
      </div>
    </div>
  );
}
