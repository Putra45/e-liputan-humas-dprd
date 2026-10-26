"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import type { OrderDetail } from "@/lib/types";
import { Dialog } from "./dialog";

interface Props {
  id: number;
  onClose: () => void;
  onSaved: (message: string) => void;
}

export function ApprovalModal({ id, onClose, onSaved }: Props) {
  const [detail, setDetail] = useState<OrderDetail | null>(null);
  const [loadError, setLoadError] = useState("");
  const [formError, setFormError] = useState("");
  const [saving, setSaving] = useState(false);
  const [status, setStatus] = useState("pending");
  const [waktu, setWaktu] = useState("");
  const [catatan, setCatatan] = useState("");

  useEffect(() => {
    api
      .orderDetail(id)
      .then((d) => {
        setDetail(d);
        setStatus(d.order.Status || "pending");
        setWaktu(d.waktu_disetujui_formatted || "");
        setCatatan(d.order.CatatanAdmin?.String || "");
      })
      .catch(() => setLoadError("Gagal mengambil data permohonan."));
  }, [id]);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setFormError("");
    setSaving(true);
    const form = new FormData();
    form.set("status", status);
    form.set("waktu_disetujui", waktu);
    form.set("catatan_admin", catatan);
    try {
      await api.updateStatus(id, form);
      onSaved("Keputusan tersimpan dan notifikasi WhatsApp telah diproses.");
    } catch (err) {
      setFormError(err instanceof Error ? err.message : "Gagal menyimpan keputusan.");
    } finally {
      setSaving(false);
    }
  }

  async function handleDelete() {
    if (!window.confirm("Hapus data permohonan ini?")) return;
    setSaving(true);
    try {
      await api.deleteOrder(id);
      onSaved("Permohonan telah dihapus.");
    } catch (err) {
      setFormError(err instanceof Error ? err.message : "Gagal menghapus.");
      setSaving(false);
    }
  }

  const inputClass =
    "w-full px-3 py-2 rounded-lg bg-surface border border-line text-sm text-ink placeholder:text-muted focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition";

  return (
    <Dialog open onClose={onClose} labelledBy="approval-title" size="md">
      <div className="px-5 py-4 border-b border-line flex items-start justify-between gap-3">
        <div>
          <h2 id="approval-title" className="text-sm font-semibold text-ink">
            Verifikasi permohonan
          </h2>
          <p className="text-xs text-muted mt-0.5">
            {detail ? `Kode ${detail.order.KodeBooking}` : "Memuat..."}
          </p>
        </div>
        <button
          type="button"
          onClick={onClose}
          aria-label="Tutup"
          className="p-1.5 rounded-lg text-muted hover:text-ink hover:bg-surface-2 transition-colors"
        >
          <svg className="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      {loadError ? (
        <div className="p-6 text-sm text-rose-700 dark:text-rose-300">{loadError}</div>
      ) : !detail ? (
        <div className="p-6 text-sm text-muted">Memuat data...</div>
      ) : (
        <form onSubmit={handleSubmit} className="flex flex-col flex-1 min-h-0">
          <div className="overflow-y-auto px-5 py-5 space-y-4 text-sm">
            {formError && (
              <div
                role="alert"
                className="p-3 rounded-lg border border-rose-200 bg-rose-50 text-sm text-rose-800 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-200"
              >
                {formError}
              </div>
            )}

            <div className="rounded-lg border border-line bg-surface-2 p-3.5 space-y-2">
              <p className="font-semibold text-ink leading-snug">{detail.order.NamaKegiatan}</p>
              <dl className="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs text-muted">
                <div>
                  Pemohon <strong className="block text-ink-2 font-medium">{detail.order.NamaPemohon}</strong>
                </div>
                <div>
                  Tanggal <strong className="block text-ink-2 font-medium">{detail.tanggal_formatted}</strong>
                </div>
                <div>
                  Kategori <strong className="block text-ink-2 font-medium">{detail.order.Kategori}</strong>
                </div>
                <div>
                  Waktu{" "}
                  <strong className="block text-ink-2 font-medium">
                    {detail.waktu_mulai_formatted} WIB
                  </strong>
                </div>
              </dl>
              <p className="text-xs text-muted pt-2 border-t border-line">{detail.order.JenisPublikasi}</p>
              <div className="flex items-center justify-between gap-3 pt-2 border-t border-line">
                <span className="font-mono text-xs text-muted">WA {detail.order.NoWhatsapp}</span>
                {detail.file_url && (
                  <a
                    href={detail.file_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="px-2 py-1 rounded-md border border-line bg-surface hover:border-line-strong text-xs font-medium text-ink-2 transition-colors"
                  >
                    Dokumen
                  </a>
                )}
              </div>
            </div>

            <fieldset>
              <legend className="block text-xs font-medium text-muted mb-2">Keputusan</legend>
              <div className="grid grid-cols-3 gap-2">
                {[
                  {
                    value: "approved",
                    label: "Setujui",
                    cls: "has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 dark:has-[:checked]:bg-emerald-500/10",
                  },
                  {
                    value: "rejected",
                    label: "Tolak",
                    cls: "has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 dark:has-[:checked]:bg-rose-500/10",
                  },
                  {
                    value: "pending",
                    label: "Pending",
                    cls: "has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 dark:has-[:checked]:bg-amber-500/10",
                  },
                ].map((opt) => (
                  <label
                    key={opt.value}
                    className={`flex items-center gap-2 px-2.5 py-2 rounded-lg border border-line bg-surface cursor-pointer hover:border-line-strong transition-colors text-sm font-medium text-ink-2 ${opt.cls}`}
                  >
                    <input
                      type="radio"
                      name="status"
                      value={opt.value}
                      checked={status === opt.value}
                      onChange={() => setStatus(opt.value)}
                      className="w-4 h-4 accent-brand"
                    />
                    <span>{opt.label}</span>
                  </label>
                ))}
              </div>
            </fieldset>

            <div>
              <label htmlFor="waktu_disetujui" className="block text-xs font-medium text-muted mb-1.5">
                Jam final liputan
              </label>
              <input
                id="waktu_disetujui"
                type="time"
                value={waktu}
                onChange={(e) => setWaktu(e.target.value)}
                className={inputClass}
              />
              <p className="mt-1.5 text-xs text-muted">Ubah jika jadwal disesuaikan dari permohonan awal.</p>
            </div>

            <div>
              <label htmlFor="catatan_admin" className="block text-xs font-medium text-muted mb-1.5">
                Catatan untuk pemohon <span className="font-normal">(opsional)</span>
              </label>
              <textarea
                id="catatan_admin"
                rows={2}
                value={catatan}
                onChange={(e) => setCatatan(e.target.value)}
                placeholder="Contoh: Petugas fotografer Bpk. Dani"
                className={`${inputClass} resize-none`}
              />
            </div>
          </div>

          <div className="px-5 py-3.5 border-t border-line space-y-2">
            <button
              type="submit"
              disabled={saving}
              className="w-full py-2.5 rounded-lg bg-brand hover:brightness-110 disabled:opacity-60 text-white text-sm font-semibold transition"
            >
              {saving ? "Memproses..." : "Simpan dan kirim WhatsApp"}
            </button>
            <div className="flex gap-2">
              {detail.manual_wa_link && (
                <a
                  href={detail.manual_wa_link}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex-1 py-2 rounded-lg border border-line bg-surface-2 hover:border-line-strong text-xs font-medium text-ink-2 transition-colors text-center"
                >
                  Kirim manual via WhatsApp
                </a>
              )}
              <button
                type="button"
                onClick={handleDelete}
                disabled={saving}
                className="flex-1 py-2 rounded-lg border border-line hover:border-rose-300 hover:bg-rose-50 dark:hover:bg-rose-500/10 text-xs font-medium text-rose-600 dark:text-rose-300 transition-colors"
              >
                Hapus permohonan
              </button>
            </div>
          </div>
        </form>
      )}
    </Dialog>
  );
}
