"use client";

import { useEffect, useRef, useState } from "react";
import FullCalendar, { type CalendarRef } from "@fullcalendar/react";
import dayGridPlugin from "@fullcalendar/react/daygrid";
import listPlugin from "@fullcalendar/react/list";
import interactionPlugin from "@fullcalendar/react/interaction";
import classicThemePlugin from "@fullcalendar/react/themes/classic";
import idLocale from "@fullcalendar/react/locales/id";
import type { CalendarEvent, PortalData } from "@/lib/types";

interface Props {
  data: PortalData | null;
  events: CalendarEvent[];
  onPickDate: (date: string) => void;
}

interface EventDetail {
  nama_kegiatan: string;
  nama_pemohon: string;
  kategori: string;
  waktu: string;
  jenis_publikasi: string;
  kode_booking: string;
  status: string;
}

type ViewName = "dayGridMonth" | "listMonth";

export function PortalCalendar({ data, events, onPickDate }: Props) {
  const calRef = useRef<CalendarRef>(null);
  const [view, setView] = useState<ViewName>("dayGridMonth");
  const [title, setTitle] = useState("");
  const [detail, setDetail] = useState<EventDetail | null>(null);

  const upcoming = data?.upcomingOrders ?? [];
  // FullCalendar v7 replaced backgroundColor/borderColor/textColor with color/contrastColor.
  const calendarEvents = events.map(({ backgroundColor, textColor, ...rest }) => ({
    ...rest,
    color: backgroundColor,
    contrastColor: textColor,
  }));
  const bookedDates = new Set(events.map((e) => e.start.slice(0, 10)));

  function showDetail(p: EventDetail) {
    setDetail({
      nama_kegiatan: p.nama_kegiatan,
      nama_pemohon: p.nama_pemohon,
      kategori: p.kategori,
      waktu: p.waktu,
      jenis_publikasi: p.jenis_publikasi,
      kode_booking: p.kode_booking,
      status: p.status,
    });
  }

  function pickDay(dateStr: string) {
    const booked = events.find((e) => e.start.slice(0, 10) === dateStr);
    if (booked) {
      showDetail(booked.extendedProps as EventDetail);
      return;
    }
    onPickDate(dateStr);
  }

  function dateKey(d: Date) {
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${d.getFullYear()}-${m}-${day}`;
  }

  useEffect(() => {
    if (!detail) return;
    function onKey(e: KeyboardEvent) {
      if (e.key === "Escape") setDetail(null);
    }
    document.addEventListener("keydown", onKey);
    return () => document.removeEventListener("keydown", onKey);
  }, [detail]);

  function go(direction: -1 | 1) {
    const api = calRef.current?.getApi();
    if (!api) return;
    if (direction < 0) api.prev();
    else api.next();
  }

  function switchView(next: ViewName) {
    setView(next);
    calRef.current?.getApi().changeView(next);
  }

  return (
    <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_300px] items-start">
      <section className="bg-surface border border-line rounded-xl overflow-hidden">
        <div className="flex flex-wrap items-center gap-2 px-3 sm:px-4 py-3 border-b border-line">
          <h2 className="order-1 basis-full sm:order-2 sm:basis-auto sm:flex-1 min-w-0 text-center sm:text-left text-sm font-semibold text-ink truncate">
            {title || "Kalender"}
          </h2>

          <div className="order-2 sm:order-1 flex items-center gap-1">
            <button
              type="button"
              onClick={() => go(-1)}
              aria-label="Periode sebelumnya"
              className="w-8 h-8 grid place-items-center rounded-lg border border-line text-muted hover:text-ink hover:bg-surface-2 transition-colors"
            >
              <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <button
              type="button"
              onClick={() => go(1)}
              aria-label="Periode berikutnya"
              className="w-8 h-8 grid place-items-center rounded-lg border border-line text-muted hover:text-ink hover:bg-surface-2 transition-colors"
            >
              <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
              </svg>
            </button>
            <button
              type="button"
              onClick={() => calRef.current?.getApi().today()}
              className="ml-1 px-2.5 h-8 rounded-lg border border-line text-xs font-medium text-ink-2 hover:bg-surface-2 transition-colors"
            >
              Hari ini
            </button>
          </div>

          <div className="order-3 inline-flex rounded-lg border border-line bg-surface-2 p-0.5 shrink-0">
            {(
              [
                ["dayGridMonth", "Bulan"],
                ["listMonth", "Agenda"],
              ] as const
            ).map(([value, label]) => (
              <button
                key={value}
                type="button"
                onClick={() => switchView(value)}
                aria-pressed={view === value}
                className={`px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors ${
                  view === value ? "bg-surface text-ink shadow-sm" : "text-muted hover:text-ink-2"
                }`}
              >
                {label}
              </button>
            ))}
          </div>
        </div>

        <div className="p-2 sm:p-3 eliputan-cal">
          <FullCalendar
            ref={calRef}
            plugins={[dayGridPlugin, listPlugin, interactionPlugin, classicThemePlugin]}
            initialView="dayGridMonth"
            locale={idLocale}
            firstDay={1}
            height="auto"
            fixedWeekCount={false}
            showNonCurrentDates={false}
            borderless
            dayMaxEvents={3}
            headerToolbar={false}
            events={calendarEvents}
            eventDisplay="block"
            displayEventTime={false}
            eventClass="eliputan-event"
            eventContent={(arg) => {
              const time = String(arg.event.extendedProps.waktu ?? "").replace(" WIB", "");
              const name = String(arg.event.extendedProps.nama_kegiatan ?? arg.event.title);
              return (
                <div className="flex items-baseline gap-1 min-w-0 overflow-hidden">
                  <span className="font-semibold tabular-nums shrink-0">{time}</span>
                  <span className="truncate min-w-0">{name}</span>
                </div>
              );
            }}
            datesSet={(info) => setTitle(info.view.title)}
            dayCellTopContent={(info) => (
              <span className="eliputan-daynum">{info.dayNumberText}</span>
            )}
            dayCellTopClass="eliputan-daytop"
            dayHeaderClass={(info) =>
              info.dow === 0 || info.dow === 6 ? "eliputan-weekend-head" : ""
            }
            dayCellClass={(info) => {
              const classes = ["eliputan-day"];
              if (info.dow === 0 || info.dow === 6) classes.push("is-weekend");
              if (info.isPast) classes.push("is-past");
              if (info.isToday) classes.push("is-today");
              if (bookedDates.has(dateKey(info.date))) classes.push("is-booked");
              return classes.join(" ");
            }}
            dateClick={(info) => pickDay(info.dateStr)}
            eventClick={(info) => showDetail(info.event.extendedProps as EventDetail)}
          />
        </div>
      </section>

      <aside className="space-y-4">
        <section className="bg-surface border border-line rounded-xl p-4">
          <h3 className="text-sm font-semibold text-ink">Keterangan</h3>
          <ul className="mt-3 space-y-2.5 text-sm text-ink-2">
            <li className="flex items-start gap-2.5">
              <span className="mt-1.5 w-2 h-2 rounded-full bg-emerald-500 shrink-0" aria-hidden="true" />
              <span>
                Sudah disetujui
                <span className="block text-xs text-muted">Tim liputan terjadwal</span>
              </span>
            </li>
            <li className="flex items-start gap-2.5">
              <span className="mt-1.5 w-2 h-2 rounded-full bg-amber-500 shrink-0" aria-hidden="true" />
              <span>
                Menunggu konfirmasi
                <span className="block text-xs text-muted">Belum diverifikasi admin</span>
              </span>
            </li>
          </ul>
          <p className="mt-3.5 pt-3.5 border-t border-line text-xs text-muted leading-relaxed">
            Klik tanggal kosong untuk mengisi formulir pengajuan. Tanggal yang sudah ada agendanya terkunci &mdash;
            klik untuk melihat detailnya.
          </p>
        </section>

        <section className="bg-surface border border-line rounded-xl overflow-hidden">
          <div className="px-4 py-3 border-b border-line flex items-center justify-between">
            <h3 className="text-sm font-semibold text-ink">Agenda mendatang</h3>
            <span className="text-xs text-muted">{upcoming.length}</span>
          </div>
          {upcoming.length === 0 ? (
            <p className="px-4 py-6 text-sm text-muted">Belum ada agenda terdaftar.</p>
          ) : (
            <ul className="divide-y divide-line">
              {upcoming.slice(0, 5).map((o) => (
                <li key={o.ID} className="px-4 py-3">
                  <div className="flex items-start gap-2.5">
                    <span
                      className={`mt-1.5 w-2 h-2 rounded-full shrink-0 ${
                        o.IsApproved ? "bg-emerald-500" : "bg-amber-500"
                      }`}
                      aria-hidden="true"
                    />
                    <div className="min-w-0">
                      <p className="text-xs text-muted">
                        {o.TanggalFormatted} &middot; {o.WaktuFinal} WIB
                      </p>
                      <p className="text-sm font-medium text-ink leading-snug mt-0.5">{o.NamaKegiatan}</p>
                      <p className="text-xs text-muted mt-0.5">{o.Kategori}</p>
                    </div>
                  </div>
                </li>
              ))}
            </ul>
          )}
        </section>
      </aside>

      {detail && (
        <div
          role="presentation"
          onClick={(e) => {
            if (e.target === e.currentTarget) setDetail(null);
          }}
          className="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4"
        >
          <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="event-title"
            className="bg-surface border border-line rounded-xl shadow-2xl max-w-md w-full overflow-hidden"
          >
            <div className="px-5 py-4 border-b border-line flex items-start justify-between gap-3">
              <div>
                <p className="text-[11px] uppercase tracking-[0.12em] text-muted">Detail jadwal</p>
                <h3 id="event-title" className="text-sm font-semibold text-ink mt-1 leading-snug">
                  {detail.nama_kegiatan}
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setDetail(null)}
                aria-label="Tutup"
                className="p-1 rounded-md text-muted hover:text-ink hover:bg-surface-2 transition-colors"
              >
                <svg className="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <dl className="p-5 space-y-3 text-sm">
              <div className="flex justify-between gap-4">
                <dt className="text-muted">Status</dt>
                <dd>
                  <span
                    className={`px-2 py-0.5 rounded-md text-xs font-semibold ${
                      detail.status === "approved"
                        ? "bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"
                        : "bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300"
                    }`}
                  >
                    {detail.status === "approved" ? "Disetujui" : "Menunggu"}
                  </span>
                </dd>
              </div>
              <div className="flex justify-between gap-4">
                <dt className="text-muted">Pemohon</dt>
                <dd className="font-medium text-ink text-right">{detail.nama_pemohon}</dd>
              </div>
              <div className="flex justify-between gap-4">
                <dt className="text-muted">Kategori</dt>
                <dd className="font-medium text-ink text-right">{detail.kategori}</dd>
              </div>
              <div className="flex justify-between gap-4">
                <dt className="text-muted">Waktu</dt>
                <dd className="font-medium text-ink text-right">{detail.waktu}</dd>
              </div>
              <div className="flex justify-between gap-4">
                <dt className="text-muted">Publikasi</dt>
                <dd className="font-medium text-ink text-right max-w-[60%]">{detail.jenis_publikasi}</dd>
              </div>
              <div className="flex justify-between gap-4 pt-3 border-t border-line">
                <dt className="text-muted">Kode</dt>
                <dd className="font-mono text-xs font-semibold text-ink-2">{detail.kode_booking}</dd>
              </div>
            </dl>
          </div>
        </div>
      )}
    </div>
  );
}
