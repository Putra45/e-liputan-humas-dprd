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
      const res = await api.adminOrders({
        search,
        status,
        tanggal,
        page: String(page),
      });
      setData(res);
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
      <div className="max-w-6xl mx-auto px-4 sm:px-6 py-16 text-center text-sm text-slate-500">
        Memeriksa sesi...
      </div>
    );
  }

  const stats = data?.stats;
  const orders = data?.orders ?? [];

  return (
    <div className="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-5">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-bold text-slate-900">Verifikasi Permohonan Liputan</h1>
          <p className="text-sm text-slate-500 mt-0.5">Kelola permohonan, jadwal personel, dan notifikasi WhatsApp.</p>
        </div>
        <div className="flex items-center gap-2">
          <Link
            href="/"
            target="_blank"
            className="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 font-medium text-xs transition-colors"
          >
            Lihat portal
          </Link>
          <button
            type="button"
            onClick={handleLogout}
            className="px-3 py-2 rounded-lg border border-slate-200 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-700 text-slate-600 font-medium text-xs transition-colors"
          >
            Keluar
          </button>
        </div>
      </div>

      {notice && (
        <div role="status" className="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-900">
          {notice}
        </div>
      )}
      {error && (
        <div role="alert" className="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-800">
          {error}
        </div>
      )}

      <div className="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <StatCard label="Total" value={stats?.total} />
        <StatCard label="Pending" value={stats?.pending} tone="amber" />
        <StatCard label="Disetujui" value={stats?.approved} tone="emerald" />
        <StatCard label="Ditolak" value={stats?.rejected} tone="rose" />
        <StatCard label="Agenda hari ini" value={stats?.today} tone="navy" />
      </div>

      <form
        onSubmit={applyFilters}
        className="bg-white p-4 rounded-xl border border-slate-200 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end"
      >
        <div>
          <label htmlFor="search" className="block text-xs font-medium text-slate-500 mb-1">
            Cari
          </label>
          <input
            id="search"
            type="search"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Pemohon, kegiatan, kode..."
            className="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-navy-600 focus:ring-2 focus:ring-navy-600/15 outline-none transition"
          />
        </div>
        <div>
          <label htmlFor="status" className="block text-xs font-medium text-slate-500 mb-1">
            Status
          </label>
          <select
            id="status"
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            className="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-navy-600 focus:ring-2 focus:ring-navy-600/15 outline-none transition"
          >
            <option value="all">Semua</option>
            <option value="pending">Pending</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
          </select>
        </div>
        <div>
          <label htmlFor="tanggal" className="block text-xs font-medium text-slate-500 mb-1">
            Tanggal
          </label>
          <input
            id="tanggal"
            type="date"
            value={tanggal}
            onChange={(e) => setTanggal(e.target.value)}
            className="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-navy-600 focus:ring-2 focus:ring-navy-600/15 outline-none transition"
          />
        </div>
        <div className="flex gap-2">
          <button
            type="submit"
            className="flex-1 py-2 px-3 rounded-lg bg-navy-800 hover:bg-navy-700 text-white font-semibold text-sm transition-colors"
          >
            Filter
          </button>
          <button
            type="button"
            onClick={resetFilters}
            className="py-2 px-3 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium text-sm transition-colors"
          >
            Reset
          </button>
        </div>
      </form>

      <div className="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50 text-slate-500 text-xs font-medium border-b border-slate-200">
              <tr>
                <th className="py-3 px-4">Booking</th>
                <th className="py-3 px-4">Pemohon</th>
                <th className="py-3 px-4">Kegiatan</th>
                <th className="py-3 px-4">Jadwal</th>
                <th className="py-3 px-4">Dokumen</th>
                <th className="py-3 px-4">Status</th>
                <th className="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {loading ? (
                <tr>
                  <td colSpan={7} className="py-10 text-center text-slate-500 text-sm">
                    Memuat data...
                  </td>
                </tr>
              ) : orders.length === 0 ? (
                <tr>
                  <td colSpan={7} className="py-10 text-center text-slate-500 text-sm">
                    Tidak ada permohonan yang sesuai filter.
                  </td>
                </tr>
              ) : (
                orders.map((o) => (
                  <tr key={o.ID} className="hover:bg-slate-50/70 transition-colors">
                    <td className="py-3 px-4 whitespace-nowrap">
                      <span className="font-mono font-semibold text-slate-900 text-xs block">{o.KodeBooking}</span>
                      <span className="text-slate-400 text-xs">{o.TanggalKegiatan?.slice(0, 10)}</span>
                    </td>
                    <td className="py-3 px-4">
                      <div className="font-medium text-slate-900">{o.NamaPemohon}</div>
                      <div className="text-xs text-slate-400 font-mono mt-0.5">{o.NoWhatsapp}</div>
                    </td>
                    <td className="py-3 px-4 max-w-[200px]">
                      <div className="font-medium text-slate-800 truncate">{o.NamaKegiatan}</div>
                      <span className="text-xs text-slate-500">{o.Kategori}</span>
                    </td>
                    <td className="py-3 px-4 whitespace-nowrap">
                      <div className="text-slate-600">{o.WaktuFinal} WIB</div>
                      {o.Status === "approved" && o.WaktuDisetujui.Valid && (
                        <div className="text-emerald-600 text-xs font-medium mt-0.5">ACC</div>
                      )}
                    </td>
                    <td className="py-3 px-4 whitespace-nowrap">
                      {o.HasFile ? (
                        <a
                          href={o.FileURL}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="inline-flex items-center gap-1 px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors"
                        >
                          Lihat
                        </a>
                      ) : (
                        <span className="text-slate-400 text-xs">-</span>
                      )}
                    </td>
                    <td className="py-3 px-4 whitespace-nowrap">
                      <StatusPill status={o.Status} label={o.StatusLabel} />
                    </td>
                    <td className="py-3 px-4 whitespace-nowrap text-right">
                      <button
                        type="button"
                        onClick={() => setOpenId(o.ID)}
                        className="px-2.5 py-1.5 rounded-lg bg-navy-800 hover:bg-navy-700 text-white font-medium text-xs transition-colors"
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
          <div className="p-4 border-t border-slate-100 flex items-center justify-center gap-3">
            {data?.hasPrev && (
              <button
                type="button"
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                className="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-medium transition-colors"
              >
                Sebelumnya
              </button>
            )}
            <span className="text-xs text-slate-500">
              Halaman {data?.page} dari {data?.totalPages}
            </span>
            {data?.hasNext && (
              <button
                type="button"
                onClick={() => setPage((p) => p + 1)}
                className="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-medium transition-colors"
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
  tone = "slate",
}: {
  label: string;
  value?: number;
  tone?: "slate" | "amber" | "emerald" | "rose" | "navy";
}) {
  const tones: Record<string, string> = {
    slate: "bg-white border-slate-200 text-slate-900",
    amber: "bg-white border-amber-200 text-amber-600",
    emerald: "bg-white border-emerald-200 text-emerald-600",
    rose: "bg-white border-rose-200 text-rose-600",
    navy: "bg-navy-800 border-navy-800 text-white",
  };
  return (
    <div className={`p-4 rounded-xl border ${tones[tone]}`}>
      <p className={`text-xs font-medium ${tone === "navy" ? "text-slate-300" : "text-slate-500"}`}>{label}</p>
      <p className="text-2xl font-bold mt-1">{value ?? "-"}</p>
    </div>
  );
}

function StatusPill({ status, label }: { status: string; label: string }) {
  const styles: Record<string, string> = {
    approved: "bg-emerald-100 text-emerald-800",
    rejected: "bg-rose-100 text-rose-800",
    pending: "bg-amber-100 text-amber-800",
  };
  return (
    <span className={`inline-flex px-2 py-0.5 rounded text-xs font-semibold ${styles[status] ?? styles.pending}`}>
      {label}
    </span>
  );
}
