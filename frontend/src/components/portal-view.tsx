"use client";

import { useCallback, useEffect, useState } from "react";
import dynamic from "next/dynamic";
import { api, ensureCsrf } from "@/lib/api";
import type { CalendarEvent, PortalData } from "@/lib/types";
import { Dialog } from "./dialog";
import { PortalForm } from "./portal-form";

// FullCalendar v7 reads the current time during render, which Next refuses to prerender.
const PortalCalendar = dynamic(() => import("./portal-calendar").then((m) => m.PortalCalendar), {
  ssr: false,
  loading: () => (
    <div className="bg-surface border border-line rounded-xl p-16 text-center text-sm text-muted">
      Memuat kalender...
    </div>
  ),
});

export function PortalView() {
  const [data, setData] = useState<PortalData | null>(null);
  const [events, setEvents] = useState<CalendarEvent[]>([]);
  const [error, setError] = useState("");
  const [formDate, setFormDate] = useState<string | null>(null);

  const load = useCallback(async () => {
    try {
      const [portal, evts] = await Promise.all([api.portal(), api.events()]);
      setData(portal);
      setEvents(evts ?? []);
      setError("");
    } catch {
      setError("Gagal memuat jadwal. Coba muat ulang halaman.");
    }
  }, []);

  useEffect(() => {
    ensureCsrf().catch(() => {});
    load();
  }, [load]);

  const upcomingCount = data?.upcomingOrders?.length ?? 0;
  const bookedDates = new Set(events.map((e) => e.start.slice(0, 10)));

  // "Ajukan liputan" should not prefill a date that is already taken.
  function defaultDate() {
    const d = new Date();
    for (let i = 0; i < 60; i++) {
      const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(
        d.getDate(),
      ).padStart(2, "0")}`;
      if (!bookedDates.has(key)) return key;
      d.setDate(d.getDate() + 1);
    }
    return data?.today ?? "";
  }

  return (
    <div className="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-5">
      <section className="overflow-hidden rounded-2xl border border-navy-900/10 dark:border-line bg-navy-800 dark:bg-surface-2">
        <div className="px-5 sm:px-8 py-7 sm:py-8 grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
          <div className="max-w-2xl">
            <p className="text-[11px] font-semibold uppercase tracking-[0.16em] text-accent-400 dark:text-accent-500">
              Humas &amp; Protokol &middot; DPRD Kota Tangerang
            </p>
            <h1 className="mt-2 text-2xl sm:text-3xl font-bold tracking-tight text-white dark:text-ink">
              Pemesanan peliputan &amp; dokumentasi
            </h1>
            <p className="mt-2.5 text-sm text-slate-300 dark:text-muted leading-relaxed">
              Lihat jadwal tim di kalender, lalu klik tanggal untuk mengajukan permohonan liputan. Semua
              pengajuan diverifikasi Humas sebelum masuk agenda.
            </p>
          </div>
          <div className="flex flex-wrap gap-2.5">
            <button
              type="button"
              onClick={() => setFormDate(defaultDate())}
              className="px-4 py-2.5 rounded-lg bg-accent-500 hover:bg-accent-400 text-navy-900 text-sm font-semibold transition-colors"
            >
              Ajukan liputan
            </button>
            <a
              href="#kalender"
              className="px-4 py-2.5 rounded-lg border border-white/20 dark:border-line bg-white/5 dark:bg-surface-2 text-sm font-medium text-white dark:text-ink-2 hover:bg-white/10 dark:hover:bg-surface-3 transition-colors"
            >
              Lihat jadwal
            </a>
          </div>
        </div>

        <dl className="grid grid-cols-2 sm:grid-cols-3 border-t border-white/10 dark:border-line divide-x divide-white/10 dark:divide-line">
          <div className="px-5 sm:px-8 py-3.5">
            <dt className="text-[11px] uppercase tracking-[0.1em] text-slate-400 dark:text-muted">
              Disetujui hari ini
            </dt>
            <dd className="mt-0.5 text-lg font-semibold text-white dark:text-ink">
              {data?.todayApprovedCount ?? 0}
            </dd>
          </div>
          <div className="px-5 sm:px-8 py-3.5">
            <dt className="text-[11px] uppercase tracking-[0.1em] text-slate-400 dark:text-muted">
              Agenda mendatang
            </dt>
            <dd className="mt-0.5 text-lg font-semibold text-white dark:text-ink">{upcomingCount}</dd>
          </div>
          <div className="col-span-2 sm:col-span-1 px-5 sm:px-8 py-3.5">
            <dt className="text-[11px] uppercase tracking-[0.1em] text-slate-400 dark:text-muted">Ketentuan</dt>
            <dd className="mt-0.5 text-sm font-medium text-white dark:text-ink">Maksimal order H-1</dd>
          </div>
        </dl>
      </section>

      {error && (
        <div
          role="alert"
          className="p-4 rounded-xl border border-rose-200 bg-rose-50 text-sm text-rose-800 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-200"
        >
          {error}
        </div>
      )}

      <div id="kalender">
        <PortalCalendar data={data} events={events} onPickDate={setFormDate} />
      </div>

      <Dialog
        open={formDate !== null}
        onClose={() => setFormDate(null)}
        labelledBy="form-title"
        size="lg"
      >
        {formDate !== null && (
          <PortalForm
            key={formDate}
            data={data}
            initialDate={formDate}
            bookedDates={bookedDates}
            onClose={() => setFormDate(null)}
            onSubmitted={load}
          />
        )}
      </Dialog>
    </div>
  );
}
