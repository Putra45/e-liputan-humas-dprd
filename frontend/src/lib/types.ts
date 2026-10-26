export interface CalendarEvent {
  id: string;
  title: string;
  start: string;
  backgroundColor: string;
  borderColor: string;
  textColor: string;
  extendedProps: {
    kode_booking: string;
    nama_pemohon: string;
    kategori: string;
    nama_kegiatan: string;
    waktu: string;
    jenis_publikasi: string;
    status: string;
    status_label: string;
  };
}

export interface Order {
  ID: number;
  KodeBooking: string;
  NamaPemohon: string;
  Fraksi: string;
  NoWhatsapp: string;
  NamaKegiatan: string;
  Kategori: string;
  TanggalKegiatan: string;
  WaktuMulai: string;
  WaktuDisetujui: { String: string; Valid: boolean };
  JenisPublikasi: string;
  FileDokumen: { String: string; Valid: boolean };
  Status: string;
  CatatanAdmin: { String: string; Valid: boolean };
  HasFile: boolean;
  FileURL: string;
  TanggalFormatted: string;
  WaktuFinal: string;
  StatusLabel: string;
  IsApproved: boolean;
}

export interface PortalData {
  kategoriList: string[];
  jenisPublikasiList: string[];
  todayApprovedCount: number;
  upcomingOrders: Order[] | null;
  today: string;
}

export interface OrderDetail {
  order: Order;
  tanggal_formatted: string;
  waktu_mulai_formatted: string;
  waktu_disetujui_formatted: string;
  file_url: string | null;
  manual_wa_link: string;
}
