import type { CalendarEvent, Order, OrderDetail, PortalData } from "./types";

function csrfToken(): string {
  const match = document.cookie.match(/(?:^|;\s*)csrf_token=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : "";
}

async function jsonFetch<T>(url: string, init?: RequestInit): Promise<T> {
  const res = await fetch(url, {
    ...init,
    headers: {
      ...(init?.body && !(init.body instanceof FormData)
        ? { "Content-Type": "application/json" }
        : {}),
      "X-CSRF-Token": csrfToken(),
      ...init?.headers,
    },
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const message =
      (data as { error?: string }).error ||
      (data as { errors?: string[] }).errors?.join(" ") ||
      "Terjadi kesalahan.";
    throw new Error(message);
  }
  return data as T;
}

export async function ensureCsrf(): Promise<void> {
  if (csrfToken()) return;
  await fetch("/api/auth/me");
}

export const api = {
  portal: () => jsonFetch<PortalData>("/api/portal"),
  events: () => jsonFetch<CalendarEvent[]>("/api/events"),

  createOrder: (form: FormData) =>
    jsonFetch<{ kode: string; kegiatan: string; tanggal: string; waktu: string; waLink: string }>(
      "/api/orders",
      { method: "POST", body: form },
    ),

  login: (email: string, password: string) =>
    jsonFetch<{ ok: boolean }>("/api/auth/login", {
      method: "POST",
      body: JSON.stringify({ email, password }),
    }),

  logout: () => jsonFetch<{ ok: boolean }>("/api/auth/logout", { method: "POST" }),

  me: () => jsonFetch<{ authenticated: boolean; name?: string }>("/api/auth/me"),

  adminOrders: (params: Record<string, string>) => {
    const qs = new URLSearchParams(params).toString();
    return jsonFetch<AdminOrdersResponse>(`/api/admin/orders?${qs}`);
  },

  orderDetail: (id: number) => jsonFetch<OrderDetail>(`/api/admin/orders/${id}`),

  updateStatus: (id: number, form: FormData) =>
    jsonFetch<{ ok: boolean; waLink: string }>(`/api/admin/orders/${id}/status`, {
      method: "POST",
      body: form,
    }),

  deleteOrder: (id: number) =>
    jsonFetch<{ ok: boolean }>(`/api/admin/orders/${id}/delete`, { method: "POST" }),
};

export interface AdminOrdersResponse {
  orders: Order[] | null;
  stats: {
    total: number;
    pending: number;
    approved: number;
    rejected: number;
    today: number;
  };
  page: number;
  totalPages: number;
  hasPrev: boolean;
  hasNext: boolean;
  prevPage: number;
  nextPage: number;
}
