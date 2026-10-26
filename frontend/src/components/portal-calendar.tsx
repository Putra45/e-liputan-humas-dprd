"use client";

import { useRef, useState } from "react";
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

export function PortalCalendar({ data, events, onPickDate }: Props) {
  const calRef = useRef<CalendarRef>(null);
  const [detail, setDetail] = useState<EventDetail | null>(null);

  const upcoming = data?.upcomingOrders ?? [];

  return (
    <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_300px] items-start">
      <section className="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div className="px-4 sm:px-5 py-3 border-b border-slate-200 flex items-center justify-between gap-3">
          <div>
            <h2 className="text-sm font-bold text-slate-900">Kalender Peliputan</h2>
            <p className="text-xs text-slate-500 mt-0.5">Klik tanggal untuk mengajukan permohonan.</p>
          </div>
          <div className="hidden sm:flex items-center gap-3 text-xs">
            <span className="inline-flex items-center gap-1.5 text-slate-600">
              <span className="w-2.5 h-2.5 rounded-full bg-emerald-500" aria-hidden="true" />
              Disetujui
            </span>
            <span className="inline-flex items-center gap-1.5 text-slate-600">
              <span className="w-2.5 h-2.5 rounded-full bg-amber-500" aria-hidden="true" />
              Menunggu
            </span>
          </div>
        </div>
        <div className="p-3 sm:p-4">
          <FullCalendar
            ref={calRef}
            plugins={[dayGridPlugin, listPlugin, interactionPlugin, classicThemePlugin]}
            initialView="dayGridMonth"
            locale={idLocale}
            firstDay={1}
            height="auto"
            dayMaxEvents={3}
            headerToolbar={{ left: "prev,next today", center: "title", right: "dayGridMonth,listMonth" }}
            events={events}
            dateClick={(info) => onPickDate(info.dateStr)}
            eventClick={(info) => {
              const p = info.event.extendedProps as EventDetail;
              setDetail({
                nama_kegiatan: p.nama_kegiatan,
                nama_pemohon: p.nama_pemohon,
                kategori: p.kategori,
                waktu: p.waktu,
                jenis_publikasi: p.jenis_publikasi,
                kode_booking: p.kode_booking,
                status: p.status,
              });
            }}
          />
        </div>
      </section>

      <aside className="space-y-4">
        <section className="bg-white rounded-2xl border border-slate-200 p-4">
          <h3 className="text-sm font-bold text-slate-900">Keterangan</h3>
          <ul className="mt-2.5 space-y-2 text-sm text-slate-600">
            <li className="flex items-center gap-2">
              <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0" aria-hidden="true" />
              Sudah disetujui (ACC)
            </li>
            <li className="flex items-center gap-2">
              <span className="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0" aria-hidden="true" />
              Menunggu konfirmasi
            </li>
          </ul>
          <p className="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 leading-relaxed">
            Nomor WhatsApp dan dokumen pemohon hanya dapat diakses tim Humas &amp; Protokol DPRD.
          </p>
        </section>

        <section className="bg-white rounded-2xl border border-slate-200">
          <div className="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h3 className="text-sm font-bold text-slate-900">Agenda Mendatang</h3>
            <span className="text-xs text-slate-500">{upcoming.length}</span>
          </div>
          {upcoming.length === 0 ? (
            <p className="px-4 py-6 text-sm text-slate-500">Belum ada agenda terdaftar.</p>
          ) : (
            <ul className="divide-y divide-slate-100">
              {upcoming.slice(0, 5).map((o) => (
                <li key={o.ID} className="px-4 py-3">
                  <div className="flex items-start gap-2.5">
                    <span
                      className={`mt-0.5 w-2 h-2 rounded-full shrink-0 ${
                        o.IsApproved ? "bg-emerald-500" : "bg-amber-500"
                      }`}
                      aria-hidden="true"
                    />
                    <div className="min-w-0">
                      <p className="text-xs text-slate-500">
                        {o.TanggalFormatted} &middot; {o.WaktuFinal} WIB
                      </p>
                      <p className="text-sm font-medium text-slate-900 leading-snug mt-0.5">
                        {o.NamaKegiatan}
                      </p>
                      <p className="text-xs text-slate-500 mt-0.5">{o.Kategori}</p>
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
          className="fixed inset-0 z-50 bg-slate-950/60 flex items-center justify-center p-4"
          onClick={(e) => {
            if (e.target === e.currentTarget) setDetail(null);
          }}
          onKeyDown={(e) => {
            if (e.key === "Escape") setDetail(null);
          }}
          role="presentation"
        >
          <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="event-title"
            className="bg-white rounded-2xl shadow-xl max-w-md w-full border border-slate-200 overflow-hidden"
          >
            <div className="bg-navy-800 text-white px-5 py-4 flex items-start justify-between gap-3">
              <div>
                <p className="text-[11px] uppercase tracking-wider text-slate-300">Detail Jadwal</p>
                <h3 id="event-title" className="text-sm font-bold mt-0.5">
                  {detail.nama_kegiatan}
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setDetail(null)}
                aria-label="Tutup"
                className="p-1 rounded text-slate-300 hover:text-white transition-colors"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <dl className="p-5 space-y-2.5 text-sm">
              <div className="flex justify-between gap-4">
                <dt className="text-slate-500">Status</dt>
                <dd>
                  <span
                    className={`px-2 py-0.5 rounded text-xs font-semibold ${
                      detail.status === "approved"
                        ? "bg-emerald-100 text-emerald-800"
                        : "bg-amber-100 text-amber-800"
                    }`}
                  >
                    {detail.status === "approved" ? "Disetujui" : "Menunggu"}
                  </span>
                </dd>
              </div>
              <div className="flex justify-between gap-4">
                <dt className="text-slate-500">Pemohon</dt>
                <dd className="font-medium text-slate-900 text-right">{detail.nama_pemohon}</dd>
              </div>
              <div className="flex justify-between gap-4">
                <dt className="text-slate-500">Kategori</dt>
                <dd className="font-medium text-slate-900 text-right">{detail.kategori}</dd>
              </div>
              <div className="flex justify-between gap-4">
                <dt className="text-slate-500">Waktu</dt>
                <dd className="font-medium text-slate-900 text-right">{detail.waktu}</dd>
              </div>
              <div className="flex justify-between gap-4">
                <dt className="text-slate-500">Publikasi</dt>
                <dd className="font-medium text-slate-900 text-right max-w-[60%]">{detail.jenis_publikasi}</dd>
              </div>
              <div className="flex justify-between gap-4 pt-2.5 border-t border-slate-100">
                <dt className="text-slate-500">Kode</dt>
                <dd className="font-mono font-semibold text-slate-700">{detail.kode_booking}</dd>
              </div>
            </dl>
          </div>
        </div>
      )}
    </div>
  );
}
