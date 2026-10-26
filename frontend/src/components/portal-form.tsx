"use client";

import { useState } from "react";
import { api, ensureCsrf } from "@/lib/api";
import type { PortalData } from "@/lib/types";

interface Props {
  data: PortalData | null;
  pickedDate: string;
  onBack: () => void;
  onSubmitted: () => void;
}

interface Success {
  kode: string;
  kegiatan: string;
  tanggal: string;
  waktu: string;
  waLink: string;
}

export function PortalForm({ data, pickedDate, onBack, onSubmitted }: Props) {
  const [tanggal, setTanggal] = useState(pickedDate);
  const [waktu, setWaktu] = useState("09:00");
  const [fileName, setFileName] = useState("");
  const [errors, setErrors] = useState<string[]>([]);
  const [submitting, setSubmitting] = useState(false);
  const [success, setSuccess] = useState<Success | null>(null);

  const today = data?.today ?? "";
  const isToday = tanggal !== "" && tanggal === today;

  function quickDate(offset: number) {
    const d = new Date();
    d.setDate(d.getDate() + offset);
    setTanggal(d.toISOString().slice(0, 10));
  }

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setErrors([]);
    setSubmitting(true);
    try {
      await ensureCsrf();
      const form = new FormData(e.currentTarget);
      const result = await api.createOrder(form);
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
      <div className="max-w-2xl mx-auto">
        <div className="bg-white rounded-2xl border border-emerald-300 overflow-hidden">
          <div className="p-6 border-b border-emerald-100 bg-emerald-50">
            <h2 className="text-base font-bold text-emerald-900">Permohonan berhasil dikirim</h2>
            <p className="text-sm text-emerald-800 mt-1">
              Status saat ini menunggu konfirmasi tim Humas &amp; Protokol.
            </p>
          </div>
          <div className="p-6 space-y-3 text-sm">
            <div className="flex flex-wrap items-center gap-2">
              <span className="text-slate-500">Kode booking</span>
              <strong className="font-mono text-base bg-slate-100 border border-slate-200 rounded px-2 py-0.5 select-all">
                {success.kode}
              </strong>
            </div>
            <dl className="space-y-2 text-slate-700">
              <div className="flex justify-between gap-4">
                <dt className="text-slate-500">Kegiatan</dt>
                <dd className="font-medium text-slate-900 text-right">{success.kegiatan}</dd>
              </div>
              <div className="flex justify-between gap-4">
                <dt className="text-slate-500">Jadwal</dt>
                <dd className="font-medium text-slate-900 text-right">
                  {success.tanggal} &middot; {success.waktu} WIB
                </dd>
              </div>
            </dl>
            <div className="flex flex-wrap gap-2 pt-2">
              <a
                href={success.waLink}
                target="_blank"
                rel="noopener noreferrer"
                className="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors"
              >
                Konfirmasi via WhatsApp
              </a>
              <button
                type="button"
                onClick={onBack}
                className="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors"
              >
                Lihat kalender
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-2xl mx-auto space-y-4">
      {pickedDate && (
        <div className="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between gap-3 text-sm">
          <span className="text-emerald-800">
            Tanggal dipilih dari kalender: <strong className="font-semibold">{pickedDate}</strong>
          </span>
          <button
            type="button"
            onClick={() => document.getElementById("nama_kegiatan")?.focus()}
            className="text-xs font-semibold text-emerald-800 underline underline-offset-2 hover:text-emerald-900"
          >
            Lanjut isi
          </button>
        </div>
      )}

      <form
        onSubmit={handleSubmit}
        className="bg-white rounded-2xl border border-slate-200 overflow-hidden"
      >
        <div className="px-5 sm:px-6 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
          <div>
            <h2 className="text-base font-bold text-slate-900">Formulir Pengajuan Liputan</h2>
            <p className="text-xs text-slate-500 mt-0.5">Lengkapi data permohonan kegiatan kedewanan.</p>
          </div>
          <button
            type="button"
            onClick={onBack}
            className="hidden sm:inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-navy-800 transition-colors"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
            Kalender
          </button>
        </div>

        <div className="p-5 sm:p-6 space-y-7">
          {errors.length > 0 && (
            <div role="alert" className="p-3 rounded-lg bg-rose-50 border border-rose-200 text-sm text-rose-800">
              <p className="font-semibold mb-1">Mohon periksa kembali:</p>
              <ul className="list-disc list-inside space-y-0.5">
                {errors.map((e) => (
                  <li key={e}>{e}</li>
                ))}
              </ul>
            </div>
          )}

          <fieldset className="space-y-4">
            <legend className="flex items-center gap-2 pb-2 border-b border-slate-200 w-full">
              <span className="w-6 h-6 rounded bg-navy-800 text-white text-xs font-bold flex items-center justify-center">
                1
              </span>
              <span className="text-sm font-bold text-slate-900">Detail Agenda</span>
            </legend>

            <div>
              <label htmlFor="nama_kegiatan" className="block text-sm font-medium text-slate-700 mb-1">
                Nama kegiatan <span className="text-rose-500">*</span>
              </label>
              <input
                id="nama_kegiatan"
                name="nama_kegiatan"
                required
                placeholder="Contoh: Rapat Dengar Pendapat Komisi II"
                className="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:border-navy-600 focus:ring-2 focus:ring-navy-600/15 outline-none transition"
              />
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label htmlFor="tanggal_kegiatan" className="block text-sm font-medium text-slate-700 mb-1">
                  Tanggal <span className="text-rose-500">*</span>
                </label>
                <input
                  id="tanggal_kegiatan"
                  name="tanggal_kegiatan"
                  type="date"
                  required
                  min={today}
                  value={tanggal}
                  onChange={(e) => setTanggal(e.target.value)}
                  className="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:border-navy-600 focus:ring-2 focus:ring-navy-600/15 outline-none transition"
                />
                <div className="flex gap-1.5 mt-1.5 text-xs">
                  <button type="button" onClick={() => quickDate(0)} className="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium transition-colors">
                    Hari ini
                  </button>
                  <button type="button" onClick={() => quickDate(1)} className="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium transition-colors">
                    Besok
                  </button>
                  <button type="button" onClick={() => quickDate(2)} className="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium transition-colors">
                    Lusa
                  </button>
                </div>
                {isToday && (
                  <p className="mt-1.5 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1.5">
                    Pemesanan hari H tetap diproses, dengan prioritas agenda resmi.
                  </p>
                )}
              </div>

              <div>
                <label htmlFor="waktu_mulai" className="block text-sm font-medium text-slate-700 mb-1">
                  Waktu mulai <span className="text-rose-500">*</span>
                </label>
                <input
                  id="waktu_mulai"
                  name="waktu_mulai"
                  type="time"
                  required
                  value={waktu}
                  onChange={(e) => setWaktu(e.target.value)}
                  className="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:border-navy-600 focus:ring-2 focus:ring-navy-600/15 outline-none transition"
                />
                <div className="flex gap-1.5 mt-1.5 text-xs">
                  {["09:00", "10:00", "13:30", "19:00"].map((t) => (
                    <button
                      key={t}
                      type="button"
                      onClick={() => setWaktu(t)}
                      className="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium transition-colors"
                    >
                      {t}
                    </button>
                  ))}
                </div>
              </div>
            </div>

            <div>
              <span className="block text-sm font-medium text-slate-700 mb-2">
                Kategori agenda <span className="text-rose-500">*</span>
              </span>
              <div className="grid grid-cols-2 sm:grid-cols-3 gap-2">
                {(data?.kategoriList ?? []).map((k, i) => (
                  <label
                    key={k}
                    className="flex items-center gap-2.5 p-3 rounded-lg border border-slate-300 hover:bg-slate-50 cursor-pointer transition-colors has-[:checked]:border-navy-600 has-[:checked]:bg-navy-50 text-sm font-medium text-slate-700"
                  >
                    <input
                      type="radio"
                      name="kategori"
                      value={k}
                      defaultChecked={i === 0}
                      className="w-4 h-4 accent-navy-800"
                    />
                    <span>{k}</span>
                  </label>
                ))}
              </div>
            </div>

            <div>
              <span className="block text-sm font-medium text-slate-700 mb-2">
                Jenis publikasi <span className="text-rose-500">*</span>
              </span>
              <div className="space-y-2">
                {(data?.jenisPublikasiList ?? []).map((j, i) => (
                  <label
                    key={j}
                    className="flex items-start gap-2.5 p-3 rounded-lg border border-slate-300 hover:bg-slate-50 cursor-pointer transition-colors has-[:checked]:border-navy-600 has-[:checked]:bg-navy-50 text-sm text-slate-700"
                  >
                    <input
                      type="radio"
                      name="jenis_publikasi"
                      value={j}
                      defaultChecked={i === 0}
                      className="mt-0.5 w-4 h-4 accent-navy-800"
                    />
                    <span>{j}</span>
                  </label>
                ))}
              </div>
            </div>
          </fieldset>

          <fieldset className="space-y-4">
            <legend className="flex items-center gap-2 pb-2 border-b border-slate-200 w-full">
              <span className="w-6 h-6 rounded bg-navy-800 text-white text-xs font-bold flex items-center justify-center">
                2
              </span>
              <span className="text-sm font-bold text-slate-900">Data Pemohon</span>
            </legend>

            <div>
              <label htmlFor="nama_pemohon" className="block text-sm font-medium text-slate-700 mb-1">
                Nama pemohon <span className="text-rose-500">*</span>
              </label>
              <input
                id="nama_pemohon"
                name="nama_pemohon"
                required
                placeholder="Contoh: H. Rusdi Alam, M.Si"
                className="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:border-navy-600 focus:ring-2 focus:ring-navy-600/15 outline-none transition"
              />
            </div>

            <div>
              <label htmlFor="no_whatsapp" className="block text-sm font-medium text-slate-700 mb-1">
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
                className="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:border-navy-600 focus:ring-2 focus:ring-navy-600/15 outline-none transition"
              />
              <p className="mt-1 text-xs text-slate-500">
                Konfirmasi ACC dan jadwal tim dikirim ke nomor ini.
              </p>
            </div>
          </fieldset>

          <fieldset className="space-y-3">
            <legend className="flex items-center gap-2 pb-2 border-b border-slate-200 w-full">
              <span className="w-6 h-6 rounded bg-navy-800 text-white text-xs font-bold flex items-center justify-center">
                3
              </span>
              <span className="text-sm font-bold text-slate-900">
                Dokumen pendukung <span className="font-normal text-slate-400">(opsional)</span>
              </span>
            </legend>

            <div className="p-5 border border-dashed border-slate-300 rounded-lg hover:border-navy-400 transition-colors text-center bg-slate-50/60">
              <input
                type="file"
                name="file_dokumen"
                id="file_dokumen"
                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                className="sr-only"
                onChange={(e) => setFileName(e.target.files?.[0]?.name ?? "")}
              />
              <label htmlFor="file_dokumen" className="cursor-pointer flex flex-col items-center gap-1.5">
                <svg className="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={1.5}
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                  />
                </svg>
                <span className="text-sm font-medium text-slate-700">Pilih surat tugas, rundown, atau flyer</span>
                <span className="text-xs text-slate-500">PDF, DOCX, JPG, PNG, maksimal 10 MB</span>
                {fileName && <span className="text-xs font-semibold text-emerald-700 mt-1">{fileName}</span>}
              </label>
            </div>
          </fieldset>

          <div className="pt-1 space-y-2">
            <button
              type="submit"
              disabled={submitting}
              className="w-full py-3 rounded-lg bg-navy-800 hover:bg-navy-700 disabled:opacity-60 text-white font-semibold text-sm transition-colors"
            >
              {submitting ? "Mengirim..." : "Kirim Pengajuan Liputan"}
            </button>
            <button
              type="button"
              onClick={onBack}
              className="w-full py-2 text-sm font-medium text-slate-500 hover:text-slate-700 transition-colors"
            >
              Batal
            </button>
          </div>
        </div>
      </form>
    </div>
  );
}
