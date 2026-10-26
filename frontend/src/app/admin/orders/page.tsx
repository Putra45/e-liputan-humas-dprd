"use client";

import { useCallback, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { api, ensureCsrf, type AdminOrdersResponse } from "@/lib/api";
import { ApprovalModal } from "@/components/approval-modal";

export default function AdminOrdersPage() {
  const router = useRouter();
  const [ready, setReady] = useState(false);
  const [data, setData] = useState<AdminOrdersResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [notice, setNotice] = useState("");
  const [openId, setOpenId] = useState<number | null>(null);

  const [search, setSearch] = useState("");
  const [status, setStatus] = useState("all");
  const [tanggal, setTanggal] = useState("");
  const [page, setPage] = useState(1);

  useEffect(() => {
    ensureCsrf()
      .then(() => api.me())
      .then((me) => {
        if (!me.authenticated) {
          router.replace("/login");
          return;
        }
        setReady(true);
      })
      .catch(() => router.replace("/login"));
  }, [router]);

  const load = useCallback(async () => {
    setLoading(true);
    setError("");
    try {
      setData(await api.adminOrders({ search, status, tanggal, page: String(page) }));
    } catch (err) {
      if (err instanceof Error && /unauth/i.test(err.message)) {
        router.replace("/login");
        return;
      }
      setError(err instanceof Error ? err.message : "Gagal memuat data.");
    } finally {
      setLoading(false);
    }
  }, [search, status, tanggal, page, router]);

  useEffect(() => {
    if (ready) load();
  }, [ready, load]);

  async function handleLogout() {
    await api.logout().catch(() => {});
    router.push("/login");
  }

  function applyFilters(e: React.FormEvent) {
    e.preventDefault();
    setPage(1);
    load();
  }

  function resetFilters() {
    setSearch("");
    setStatus("all");
    setTanggal("");
    setPage(1);
  }

  if (!ready) {
    return (
      <div className="max-w-6xl mx-auto px-4 sm:px-6 py-16 text-center text-sm text-muted">
        Memeriksa sesi...
      </div>
    );
  }

  const stats = data?.stats;
  const orders = data?.orders ?? [];
  const inputClass =
    "w-full px-3 py-2 text-sm rounded-lg bg-surface border border-line text-ink placeholder:text-muted focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition";

  return (
    <div className="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-5">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-bold text-ink">Verifikasi permohonan</h1>
          <p className="text-sm text-muted mt-0.5">
            Kelola permohonan, jadwal personel, dan notifikasi WhatsApp.
          </p>
        </div>
        <div className="flex items-center gap-2">
          <Link
            href="/"
            target="_blank"
            className="px-3 py-2 rounded-lg border border-line text-xs font-medium text-ink-2 hover:bg-surface-2 transition-colors"
          >
            Lihat portal
          </Link>
          <button
            type="button"
            onClick={handleLogout}
            className="px-3 py-2 rounded-lg border border-line text-xs font-medium text-ink-2 hover:bg-surface-2 transition-colors"
          >
            Keluar
          </button>
        </div>
      </div>

      {notice && (
        <div
          role="status"
          className="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50 text-sm text-emerald-900 dark:border-emerald-500/25 dark:bg-emerald-500/10 dark:text-emerald-200"
        >
          {notice}
        </div>
      )}
      {error && (
        <div
          role="alert"
          className="p-3.5 rounded-xl border border-rose-200 bg-rose-50 text-sm text-rose-800 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-200"
        >
          {error}
        </div>
      )}

      <div className="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <StatCard label="Total" value={stats?.total} />
        <StatCard label="Pending" value={stats?.pending} tone="amber" />
        <StatCard label="Disetujui" value={stats?.approved} tone="emerald" />
        <StatCard label="Ditolak" value={stats?.rejected} tone="rose" />
        <StatCard label="Hari ini" value={stats?.today} tone="brand" />
      </div>

      <form
        onSubmit={applyFilters}
        className="bg-surface border border-line p-4 rounded-xl grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end"
      >
        <div>
          <label htmlFor="search" className="block text-xs font-medium text-muted mb-1">
            Cari
          </label>
          <input
            id="search"
            type="search"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Pemohon, kegiatan, kode..."
            className={inputClass}
          />
        </div>
        <div>
          <label htmlFor="status" className="block text-xs font-medium text-muted mb-1">
            Status
          </label>
          <select
            id="status"
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            className={inputClass}
          >
            <option value="all">Semua</option>
            <option value="pending">Pending</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
          </select>
        </div>
        <div>
          <label htmlFor="tanggal" className="block text-xs font-medium text-muted mb-1">
            Tanggal
          </label>
          <input
            id="tanggal"
            type="date"
            value={tanggal}
            onChange={(e) => setTanggal(e.target.value)}
            className={inputClass}
          />
        </div>
        <div className="flex gap-2">
          <button
            type="submit"
            className="flex-1 py-2 px-3 rounded-lg bg-brand hover:brightness-110 text-white text-sm font-semibold transition"
          >
            Filter
          </button>
          <button
            type="button"
            onClick={resetFilters}
            className="py-2 px-3 rounded-lg bg-surface-2 border border-line text-sm font-medium text-ink-2 hover:border-line-strong transition-colors"
          >
            Reset
          </button>
        </div>
      </form>

      <div className="bg-surface border border-line rounded-xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm">
            <thead className="bg-surface-2 text-muted text-xs font-medium border-b border-line">
              <tr>
                <th className="py-3 px-4 font-medium">Booking</th>
                <th className="py-3 px-4 font-medium">Pemohon</th>
                <th className="py-3 px-4 font-medium">Kegiatan</th>
                <th className="py-3 px-4 font-medium">Jadwal</th>
                <th className="py-3 px-4 font-medium">Dokumen</th>
                <th className="py-3 px-4 font-medium">Status</th>
                <th className="py-3 px-4 font-medium text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-line">
              {loading ? (
                <tr>
                  <td colSpan={7} className="py-10 text-center text-sm text-muted">
                    Memuat data...
                  </td>
                </tr>
              ) : orders.length === 0 ? (
                <tr>
                  <td colSpan={7} className="py-10 text-center text-sm text-muted">
                    Tidak ada permohonan yang sesuai filter.
                  </td>
                </tr>
              ) : (
                orders.map((o) => (
                  <tr key={o.ID} className="hover:bg-surface-2 transition-colors">
                    <td className="py-3 px-4 whitespace-nowrap">
                      <span className="font-mono font-semibold text-ink text-xs block">{o.KodeBooking}</span>
                      <span className="text-muted text-xs">{o.TanggalKegiatan?.slice(0, 10)}</span>
                    </td>
                    <td className="py-3 px-4">
                      <div className="font-medium text-ink">{o.NamaPemohon}</div>
                      <div className="text-xs text-muted font-mono mt-0.5">{o.NoWhatsapp}</div>
                    </td>
                    <td className="py-3 px-4 max-w-[220px]">
                      <div className="font-medium text-ink-2 truncate">{o.NamaKegiatan}</div>
                      <span className="text-xs text-muted">{o.Kategori}</span>
                    </td>
                    <td className="py-3 px-4 whitespace-nowrap">
                      <div className="text-ink-2">{o.WaktuFinal} WIB</div>
                      {o.Status === "approved" && o.WaktuDisetujui.Valid && (
                        <div className="text-emerald-600 dark:text-emerald-400 text-xs font-medium mt-0.5">
                          ACC
                        </div>
                      )}
                    </td>
                    <td className="py-3 px-4 whitespace-nowrap">
                      {o.HasFile ? (
                        <a
                          href={o.FileURL}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="inline-flex items-center px-2 py-1 rounded-md bg-surface-2 border border-line hover:border-line-strong text-ink-2 text-xs font-medium transition-colors"
                        >
                          Lihat
                        </a>
                      ) : (
                        <span className="text-muted text-xs">-</span>
                      )}
                    </td>
                    <td className="py-3 px-4 whitespace-nowrap">
                      <StatusPill status={o.Status} label={o.StatusLabel} />
                    </td>
                    <td className="py-3 px-4 whitespace-nowrap text-right">
                      <button
                        type="button"
                        onClick={() => setOpenId(o.ID)}
                        className="px-2.5 py-1.5 rounded-lg bg-brand hover:brightness-110 text-white text-xs font-medium transition"
                      >
                        Verifikasi
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {(data?.hasPrev || data?.hasNext) && (
          <div className="p-4 border-t border-line flex items-center justify-center gap-3">
            {data?.hasPrev && (
              <button
                type="button"
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                className="px-3 py-1.5 rounded-lg bg-surface-2 border border-line text-xs font-medium text-ink-2 hover:border-line-strong transition-colors"
              >
                Sebelumnya
              </button>
            )}
            <span className="text-xs text-muted">
              Halaman {data?.page} dari {data?.totalPages}
            </span>
            {data?.hasNext && (
              <button
                type="button"
                onClick={() => setPage((p) => p + 1)}
                className="px-3 py-1.5 rounded-lg bg-surface-2 border border-line text-xs font-medium text-ink-2 hover:border-line-strong transition-colors"
              >
                Selanjutnya
              </button>
            )}
          </div>
        )}
      </div>

      {openId !== null && (
        <ApprovalModal
          id={openId}
          onClose={() => setOpenId(null)}
          onSaved={(msg) => {
            setOpenId(null);
            setNotice(msg);
            load();
          }}
        />
      )}
    </div>
  );
}

function StatCard({
  label,
  value,
  tone = "neutral",
}: {
  label: string;
  value?: number;
  tone?: "neutral" | "amber" | "emerald" | "rose" | "brand";
}) {
  const tones: Record<string, string> = {
    neutral: "",
    amber: "",
    emerald: "",
    rose: "",
    brand: "",
  };
  const valueTone: Record<string, string> = {
    neutral: "text-ink",
    amber: "text-amber-600 dark:text-amber-400",
    emerald: "text-emerald-600 dark:text-emerald-400",
    rose: "text-rose-600 dark:text-rose-400",
    brand: "text-white",
  };
  return (
    <div className={`p-4 rounded-xl border ${tone === "brand" ? "border-transparent bg-brand text-white" : `bg-surface border-line ${tones[tone]}`}`}>
      <p className={`text-xs font-medium ${tone === "brand" ? "text-white/75" : "text-muted"}`}>{label}</p>
      <p className={`text-2xl font-bold mt-1 ${valueTone[tone]}`}>{value ?? "-"}</p>
    </div>
  );
}

function StatusPill({ status, label }: { status: string; label: string }) {
  const styles: Record<string, string> = {
    approved: "bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300",
    rejected: "bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300",
    pending: "bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300",
  };
  return (
    <span className={`inline-flex px-2 py-0.5 rounded-md text-xs font-semibold ${styles[status] ?? styles.pending}`}>
      {label}
    </span>
  );
}
