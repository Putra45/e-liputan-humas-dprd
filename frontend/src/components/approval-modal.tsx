"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import type { OrderDetail } from "@/lib/types";

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

  useEffect(() => {
    function onKey(e: KeyboardEvent) {
      if (e.key === "Escape") onClose();
    }
    document.addEventListener("keydown", onKey);
    return () => document.removeEventListener("keydown", onKey);
  }, [onClose]);

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

  return (
    <div
      className="fixed inset-0 z-50 bg-slate-950/60 flex items-center justify-center p-4"
      onClick={(e) => {
        if (e.target === e.currentTarget) onClose();
      }}
      role="presentation"
    >
      <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="approval-title"
        className="bg-white rounded-2xl shadow-xl max-w-lg w-full border border-slate-200 overflow-hidden max-h-[90vh] flex flex-col"
      >
        <div className="bg-navy-800 text-white px-5 py-4 flex items-start justify-between gap-3 shrink-0">
          <div>
            <h2 id="approval-title" className="text-sm font-bold">
              Verifikasi Permohonan
            </h2>
            <p className="text-xs text-slate-300 mt-0.5">
              {detail ? `Kode ${detail.order.KodeBooking}` : "Memuat..."}
            </p>
          </div>
          <button
            type="button"
            onClick={onClose}
            aria-label="Tutup"
            className="p-1 rounded text-slate-300 hover:text-white transition-colors"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        {loadError ? (
          <div className="p-6 text-sm text-rose-700">{loadError}</div>
        ) : !detail ? (
          <div className="p-6 text-sm text-slate-500">Memuat data...</div>
        ) : (
          <form onSubmit={handleSubmit} className="overflow-y-auto p-5 space-y-4 text-sm">
            {formError && (
              <div role="alert" className="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                {formError}
              </div>
            )}

            <div className="p-3.5 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
              <p className="font-semibold text-slate-900">{detail.order.NamaKegiatan}</p>
              <dl className="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-slate-600">
                <div>
                  Pemohon: <strong className="text-slate-900">{detail.order.NamaPemohon}</strong>
                </div>
                <div>
                  Tanggal: <strong className="text-slate-900">{detail.tanggal_formatted}</strong>
                </div>
                <div>
                  Kategori: <strong className="text-slate-900">{detail.order.Kategori}</strong>
                </div>
                <div>
                  Waktu: <strong className="text-slate-900">{detail.waktu_mulai_formatted} WIB</strong>
                </div>
              </dl>
              <p className="text-xs text-slate-500 pt-1.5 border-t border-slate-200">
                {detail.order.JenisPublikasi}
              </p>
              <div className="flex items-center justify-between pt-1.5 border-t border-slate-200 text-xs">
                <span className="font-mono text-slate-500">WA: {detail.order.NoWhatsapp}</span>
                {detail.file_url && (
                  <a
                    href={detail.file_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="px-2 py-1 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium transition-colors"
                  >
                    Dokumen
                  </a>
                )}
              </div>
            </div>

            <fieldset>
              <legend className="block text-xs font-medium text-slate-600 mb-2">Keputusan</legend>
              <div className="grid grid-cols-3 gap-2">
                {[
                  { value: "approved", label: "Setujui", cls: "has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50" },
                  { value: "rejected", label: "Tolak", cls: "has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50" },
                  { value: "pending", label: "Pending", cls: "has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50" },
                ].map((opt) => (
                  <label
                    key={opt.value}
                    className={`flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors text-sm font-medium ${opt.cls}`}
                  >
                    <input
                      type="radio"
                      name="status"
                      value={opt.value}
                      checked={status === opt.value}
                      onChange={() => setStatus(opt.value)}
                      className="w-4 h-4 accent-navy-800"
                    />
                    <span>{opt.label}</span>
                  </label>
                ))}
              </div>
            </fieldset>

            <div>
              <label htmlFor="waktu_disetujui" className="block text-xs font-medium text-slate-600 mb-1">
                Jam final liputan
              </label>
              <p className="text-xs text-slate-400 mb-1.5">Ubah jika jadwal disesuaikan dari permohonan awal.</p>
              <input
                id="waktu_disetujui"
                type="time"
                value={waktu}
                onChange={(e) => setWaktu(e.target.value)}
                className="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-navy-600 focus:ring-2 focus:ring-navy-600/15 outline-none transition"
              />
            </div>

            <div>
              <label htmlFor="catatan_admin" className="block text-xs font-medium text-slate-600 mb-1">
                Catatan untuk pemohon <span className="font-normal text-slate-400">(opsional)</span>
              </label>
              <textarea
                id="catatan_admin"
                rows={2}
                value={catatan}
                onChange={(e) => setCatatan(e.target.value)}
                placeholder="Contoh: Petugas fotografer Bpk. Dani"
                className="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-navy-600 focus:ring-2 focus:ring-navy-600/15 outline-none transition resize-none"
              />
            </div>

            <div className="pt-2 border-t border-slate-100 space-y-2">
              <button
                type="submit"
                disabled={saving}
                className="w-full py-2.5 rounded-lg bg-navy-800 hover:bg-navy-700 disabled:opacity-60 text-white font-semibold text-sm transition-colors"
              >
                {saving ? "Memproses..." : "Simpan dan kirim WhatsApp"}
              </button>
              {detail.manual_wa_link && (
                <a
                  href={detail.manual_wa_link}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-full py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs transition-colors flex items-center justify-center"
                >
                  Kirim manual via WhatsApp
                </a>
              )}
              <button
                type="button"
                onClick={handleDelete}
                disabled={saving}
                className="w-full py-2 rounded-lg text-rose-600 hover:bg-rose-50 font-medium text-xs transition-colors"
              >
                Hapus permohonan
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
}
