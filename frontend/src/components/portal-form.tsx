"use client";

import { useEffect, useRef, useState } from "react";
import { api, ensureCsrf } from "@/lib/api";
import type { PortalData } from "@/lib/types";

interface Props {
  data: PortalData | null;
  initialDate: string;
  bookedDates: Set<string>;
  onClose: () => void;
  onSubmitted: () => void;
}

interface Success {
  kode: string;
  kegiatan: string;
  tanggal: string;
  waktu: string;
  waLink: string;
}

const fieldClass =
  "w-full px-3.5 py-2.5 rounded-lg bg-surface border border-line text-sm text-ink placeholder:text-muted focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition";

const chipClass =
  "px-2 py-1 rounded-md bg-surface-2 border border-line hover:border-line-strong text-xs font-medium text-ink-2 transition-colors";

export function PortalForm({ data, initialDate, bookedDates, onClose, onSubmitted }: Props) {
  const [tanggal, setTanggal] = useState(initialDate);
  const [waktu, setWaktu] = useState("09:00");
  const [fileName, setFileName] = useState("");
  const [errors, setErrors] = useState<string[]>([]);
  const [submitting, setSubmitting] = useState(false);
  const [success, setSuccess] = useState<Success | null>(null);
  const errorRef = useRef<HTMLDivElement>(null);
  const dateRef = useRef<HTMLInputElement>(null);

  const today = data?.today ?? "";
  const isToday = tanggal !== "" && tanggal === today;
  const dateTaken = tanggal !== "" && bookedDates.has(tanggal);

  // Keep server-side rejections visible without the user scrolling up.
  useEffect(() => {
    if (errors.length > 0) errorRef.current?.scrollIntoView({ block: "center", behavior: "smooth" });
  }, [errors]);

  function quickDate(offset: number) {
    const d = new Date();
    d.setDate(d.getDate() + offset);
    setTanggal(d.toISOString().slice(0, 10));
  }

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = e.currentTarget;
    if (dateTaken) {
      setErrors([]);
      dateRef.current?.scrollIntoView({ block: "center", behavior: "smooth" });
      dateRef.current?.focus();
      return;
    }
    setErrors([]);
    setSubmitting(true);
    try {
      await ensureCsrf();
      const result = await api.createOrder(new FormData(form));
      setSuccess(result);
      onSubmitted();
    } catch (err) {
      setErrors([err instanceof Error ? err.message : "Gagal mengirim permohonan."]);
    } finally {
      setSubmitting(false);
    }
  }

  if (success) {
    return (
      <>
        <div className="px-5 py-4 border-b border-line">
          <h2 id="form-title" className="text-base font-semibold text-ink">
            Permohonan terkirim
          </h2>
          <p className="text-xs text-muted mt-0.5">Menunggu konfirmasi tim Humas &amp; Protokol.</p>
        </div>
        <div className="p-5 space-y-4 overflow-y-auto">
          <div className="rounded-lg border border-emerald-200 bg-emerald-50 dark:border-emerald-500/25 dark:bg-emerald-500/10 px-4 py-3">
            <p className="text-xs text-emerald-700 dark:text-emerald-300">Kode booking</p>
            <p className="font-mono text-lg font-semibold text-emerald-900 dark:text-emerald-200 select-all">
              {success.kode}
            </p>
          </div>
          <dl className="space-y-2.5 text-sm">
            <div className="flex justify-between gap-4">
              <dt className="text-muted">Kegiatan</dt>
              <dd className="font-medium text-ink text-right max-w-[65%]">{success.kegiatan}</dd>
            </div>
            <div className="flex justify-between gap-4">
              <dt className="text-muted">Jadwal</dt>
              <dd className="font-medium text-ink text-right">
                {success.tanggal} &middot; {success.waktu} WIB
              </dd>
            </div>
          </dl>
          <p className="text-xs text-muted leading-relaxed">
            Simpan kode booking ini. Konfirmasi dan penyesuaian jadwal dikirim ke nomor WhatsApp yang kamu
            daftarkan.
          </p>
        </div>
        <div className="px-5 py-3.5 border-t border-line flex flex-wrap gap-2">
          <a
            href={success.waLink}
            target="_blank"
            rel="noopener noreferrer"
            className="flex-1 min-w-[180px] text-center px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors"
          >
            Konfirmasi via WhatsApp
          </a>
          <button
            type="button"
            onClick={onClose}
            className="px-4 py-2.5 rounded-lg bg-surface-2 border border-line hover:border-line-strong text-ink-2 text-sm font-medium transition-colors"
          >
            Tutup
          </button>
        </div>
      </>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="flex flex-col flex-1 min-h-0">
      <div className="px-5 py-4 border-b border-line flex items-start justify-between gap-3">
        <div>
          <h2 id="form-title" className="text-base font-semibold text-ink">
            Formulir pengajuan liputan
          </h2>
          <p className="text-xs text-muted mt-0.5">Lengkapi data agenda kedewanan di bawah ini.</p>
        </div>
        <button
          type="button"
          onClick={onClose}
          aria-label="Tutup formulir"
          className="p-1.5 rounded-lg text-muted hover:text-ink hover:bg-surface-2 transition-colors"
        >
          <svg className="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div className="overflow-y-auto px-5 py-5 space-y-6">
        {errors.length > 0 && (
          <div
            ref={errorRef}
            role="alert"
            className="sticky top-0 z-10 p-3 rounded-lg border border-rose-200 bg-rose-50 text-sm text-rose-800 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-200 shadow-sm"
          >
            <p className="font-semibold mb-1">Mohon periksa kembali</p>
            <ul className="list-disc list-inside space-y-0.5">
              {errors.map((e) => (
                <li key={e}>{e}</li>
              ))}
            </ul>
          </div>
        )}

        <fieldset className="space-y-4">
          <legend className="w-full pb-2 border-b border-line text-xs font-semibold uppercase tracking-[0.1em] text-muted">
            Detail agenda
          </legend>

          <div>
            <label htmlFor="nama_kegiatan" className="block text-sm font-medium text-ink-2 mb-1.5">
              Nama kegiatan <span className="text-rose-500">*</span>
            </label>
            <input
              id="nama_kegiatan"
              name="nama_kegiatan"
              required
              autoFocus
              placeholder="Contoh: Rapat Dengar Pendapat Komisi II"
              className={fieldClass}
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label htmlFor="tanggal_kegiatan" className="block text-sm font-medium text-ink-2 mb-1.5">
                Tanggal <span className="text-rose-500">*</span>
              </label>
              <input
                ref={dateRef}
                id="tanggal_kegiatan"
                name="tanggal_kegiatan"
                type="date"
                required
                min={today}
                value={tanggal}
                onChange={(e) => setTanggal(e.target.value)}
                aria-invalid={dateTaken || undefined}
                className={`${fieldClass} ${
                  dateTaken ? "border-rose-400 focus:border-rose-500 focus:ring-rose-500/20" : ""
                }`}
              />
              <div className="flex gap-1.5 mt-2 text-xs">
                <button type="button" onClick={() => quickDate(0)} className={chipClass}>
                  Hari ini
                </button>
                <button type="button" onClick={() => quickDate(1)} className={chipClass}>
                  Besok
                </button>
                <button type="button" onClick={() => quickDate(2)} className={chipClass}>
                  Lusa
                </button>
              </div>
              {dateTaken && (
                <div
                  role="alert"
                  className="mt-2 flex items-start gap-2 rounded-md border border-rose-200 bg-rose-50 px-2.5 py-2 text-xs text-rose-800 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-200"
                >
                  <svg
                    className="w-4 h-4 shrink-0 mt-px"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                    />
                  </svg>
                  <span>
                    <span className="font-semibold">Tanggal sudah dibooking agenda lain.</span> Pilih tanggal
                    yang masih kosong.
                  </span>
                </div>
              )}
              {isToday && (
                <p className="mt-2 text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/25 rounded-md px-2 py-1.5">
                  Pemesanan hari H tetap diproses, dengan prioritas agenda resmi.
                </p>
              )}
            </div>

            <div>
              <label htmlFor="waktu_mulai" className="block text-sm font-medium text-ink-2 mb-1.5">
                Waktu mulai <span className="text-rose-500">*</span>
              </label>
              <input
                id="waktu_mulai"
                name="waktu_mulai"
                type="time"
                required
                value={waktu}
                onChange={(e) => setWaktu(e.target.value)}
                className={fieldClass}
              />
              <div className="flex gap-1.5 mt-2 text-xs">
                {["09:00", "10:00", "13:30", "19:00"].map((t) => (
                  <button key={t} type="button" onClick={() => setWaktu(t)} className={chipClass}>
                    {t}
                  </button>
                ))}
              </div>
            </div>
          </div>

          <div>
            <span className="block text-sm font-medium text-ink-2 mb-2">
              Kategori agenda <span className="text-rose-500">*</span>
            </span>
            <div className="grid grid-cols-2 sm:grid-cols-3 gap-2">
              {(data?.kategoriList ?? []).map((k, i) => (
                <label
                  key={k}
                  className="flex items-center gap-2.5 px-3 py-2.5 rounded-lg border border-line bg-surface cursor-pointer hover:border-line-strong has-[:checked]:border-brand has-[:checked]:bg-brand-soft dark:has-[:checked]:bg-surface-2 transition-colors text-sm text-ink-2"
                >
                  <input
                    type="radio"
                    name="kategori"
                    value={k}
                    defaultChecked={i === 0}
                    className="w-4 h-4 accent-brand"
                  />
                  <span>{k}</span>
                </label>
              ))}
            </div>
          </div>

          <div>
            <span className="block text-sm font-medium text-ink-2 mb-2">
              Jenis publikasi <span className="text-rose-500">*</span>
            </span>
            <div className="space-y-2">
              {(data?.jenisPublikasiList ?? []).map((j, i) => (
                <label
                  key={j}
                  className="flex items-start gap-2.5 px-3 py-2.5 rounded-lg border border-line bg-surface cursor-pointer hover:border-line-strong has-[:checked]:border-brand has-[:checked]:bg-brand-soft dark:has-[:checked]:bg-surface-2 transition-colors text-sm text-ink-2"
                >
                  <input
                    type="radio"
                    name="jenis_publikasi"
                    value={j}
                    defaultChecked={i === 0}
                    className="mt-0.5 w-4 h-4 accent-brand"
                  />
                  <span>{j}</span>
                </label>
              ))}
            </div>
          </div>
        </fieldset>

        <fieldset className="space-y-4">
          <legend className="w-full pb-2 border-b border-line text-xs font-semibold uppercase tracking-[0.1em] text-muted">
            Data pemohon
          </legend>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label htmlFor="nama_pemohon" className="block text-sm font-medium text-ink-2 mb-1.5">
                Nama pemohon <span className="text-rose-500">*</span>
              </label>
              <input
                id="nama_pemohon"
                name="nama_pemohon"
                required
                placeholder="Contoh: H. Rusdi Alam, M.Si"
                className={fieldClass}
              />
            </div>

            <div>
              <label htmlFor="no_whatsapp" className="block text-sm font-medium text-ink-2 mb-1.5">
                Nomor WhatsApp <span className="text-rose-500">*</span>
              </label>
              <input
                id="no_whatsapp"
                name="no_whatsapp"
                type="tel"
                required
                minLength={9}
                maxLength={20}
                placeholder="081234567890"
                className={`${fieldClass} font-mono`}
              />
              <p className="mt-1.5 text-xs text-muted">Konfirmasi ACC dikirim ke nomor ini.</p>
            </div>
          </div>
        </fieldset>

        <fieldset className="space-y-3">
          <legend className="w-full pb-2 border-b border-line text-xs font-semibold uppercase tracking-[0.1em] text-muted">
            Dokumen pendukung (opsional)
          </legend>

          <div className="rounded-lg border border-dashed border-line-strong bg-surface-2 px-4 py-5 text-center hover:border-brand transition-colors">
            <input
              type="file"
              name="file_dokumen"
              id="file_dokumen"
              accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
              className="sr-only"
              onChange={(e) => setFileName(e.target.files?.[0]?.name ?? "")}
            />
            <label htmlFor="file_dokumen" className="cursor-pointer flex flex-col items-center gap-1">
              <svg className="w-6 h-6 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={1.6}
                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                />
              </svg>
              <span className="text-sm font-medium text-ink-2">Pilih surat tugas, rundown, atau flyer</span>
              <span className="text-xs text-muted">PDF, DOCX, JPG, PNG &middot; maks 10 MB</span>
              {fileName && (
                <span className="text-xs font-medium text-emerald-700 dark:text-emerald-300 mt-1">
                  {fileName}
                </span>
              )}
            </label>
          </div>
        </fieldset>
      </div>

      <div className="px-5 py-3.5 border-t border-line flex flex-col-reverse sm:flex-row gap-2">
        <button
          type="button"
          onClick={onClose}
          className="px-4 py-2.5 rounded-lg border border-line hover:bg-surface-2 text-ink-2 text-sm font-medium transition-colors"
        >
          Batal
        </button>
        <button
          type="submit"
          disabled={submitting}
          className="flex-1 py-2.5 rounded-lg bg-brand hover:brightness-110 disabled:opacity-60 text-white text-sm font-semibold transition"
        >
          {submitting ? "Mengirim..." : "Kirim pengajuan"}
        </button>
      </div>
    </form>
  );
}
