"use client";

import { useCallback, useEffect, useState } from "react";
import dynamic from "next/dynamic";
import { api, ensureCsrf } from "@/lib/api";
import type { CalendarEvent, PortalData } from "@/lib/types";
import { PortalForm } from "./portal-form";

// FullCalendar v7 reads the current time during render, which Next refuses to prerender.
const PortalCalendar = dynamic(() => import("./portal-calendar").then((m) => m.PortalCalendar), {
  ssr: false,
  loading: () => (
    <div className="bg-white rounded-2xl border border-slate-200 p-16 text-center text-sm text-slate-500">
      Memuat kalender...
    </div>
  ),
});

type Tab = "calendar" | "form";

export function PortalView() {
  const [tab, setTab] = useState<Tab>("calendar");
  const [data, setData] = useState<PortalData | null>(null);
  const [events, setEvents] = useState<CalendarEvent[]>([]);
  const [pickedDate, setPickedDate] = useState("");
  const [error, setError] = useState("");

  const load = useCallback(async () => {
    try {
      const [portal, evts] = await Promise.all([api.portal(), api.events()]);
      setData(portal);
      setEvents(evts ?? []);
    } catch {
      setError("Gagal memuat jadwal. Coba muat ulang halaman.");
    }
  }, []);

  useEffect(() => {
    ensureCsrf().catch(() => {});
    load();
  }, [load]);

  function pickDate(date: string) {
    setPickedDate(date);
    setTab("form");
  }

  return (
    <div className="max-w-6xl mx-auto px-4 sm:px-6 py-6 space-y-5">
      <section className="rounded-2xl bg-navy-800 text-white overflow-hidden">
        <div className="px-5 sm:px-7 py-6 sm:py-7 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
          <div className="max-w-xl">
            <p className="text-[11px] font-semibold uppercase tracking-[0.14em] text-accent-400">
              Layanan Humas &amp; Protokol
            </p>
            <h1 className="mt-1.5 text-xl sm:text-2xl font-bold tracking-tight">
              Pemesanan Peliputan &amp; Dokumentasi
            </h1>
            <p className="mt-1.5 text-sm text-slate-300 leading-relaxed">
              Cek jadwal tim peliputan, lalu pilih tanggal pada kalender untuk mengajukan permohonan.
            </p>
          </div>
          <div className="flex gap-2.5 shrink-0">
            <button
              type="button"
              onClick={() => setTab("form")}
              className="px-4 py-2.5 rounded-lg bg-accent-500 hover:bg-accent-400 text-navy-900 font-semibold text-sm transition-colors"
            >
              Ajukan Liputan
            </button>
            <button
              type="button"
              onClick={() => setTab("calendar")}
              className="px-4 py-2.5 rounded-lg bg-white/10 hover:bg-white/15 border border-white/15 text-white font-medium text-sm transition-colors"
            >
              Lihat Jadwal
            </button>
          </div>
        </div>
        <div className="px-5 sm:px-7 py-2.5 bg-navy-900/50 border-t border-white/10 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-300">
          <span>
            Ketentuan: <strong className="text-white font-semibold">maksimal order H-1</strong>. Permohonan hari H
            tetap diproses dengan prioritas agenda resmi.
          </span>
          <span>
            Agenda hari ini:{" "}
            <strong className="text-white font-semibold">{data?.todayApprovedCount ?? 0} disetujui</strong>
          </span>
        </div>
      </section>

      {error && (
        <div
          role="alert"
          className="p-4 rounded-xl border border-rose-300 bg-rose-50 text-rose-800 text-sm"
        >
          {error}
        </div>
      )}

      <div className="bg-slate-200 p-1 rounded-lg max-w-sm mx-auto flex border border-slate-300">
        <button
          type="button"
          onClick={() => setTab("calendar")}
          aria-pressed={tab === "calendar"}
          className={`flex-1 py-2 px-3 rounded-md text-sm font-semibold transition-colors ${
            tab === "calendar" ? "bg-white text-navy-800 shadow-sm" : "text-slate-600 hover:text-slate-900"
          }`}
        >
          Jadwal &amp; Kalender
        </button>
        <button
          type="button"
          onClick={() => setTab("form")}
          aria-pressed={tab === "form"}
          className={`flex-1 py-2 px-3 rounded-md text-sm font-semibold transition-colors ${
            tab === "form" ? "bg-white text-navy-800 shadow-sm" : "text-slate-600 hover:text-slate-900"
          }`}
        >
          Form Pengajuan
        </button>
      </div>

      {tab === "calendar" ? (
        <PortalCalendar data={data} events={events} onPickDate={pickDate} />
      ) : (
        <PortalForm
          data={data}
          pickedDate={pickedDate}
          onBack={() => setTab("calendar")}
          onSubmitted={load}
        />
      )}
    </div>
  );
}
