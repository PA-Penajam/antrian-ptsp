import type { ApiError, ApiResponse, BookingPayload, Institution, QueueTicket, Service } from '@/types/api';

const BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';

async function apiFetch<T>(path: string, options?: RequestInit): Promise<T> {
  const res = await fetch(`${BASE_URL}${path}`, {
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...options?.headers,
    },
    ...options,
  });

  if (!res.ok) {
    const error: ApiError = await res.json().catch(() => ({ message: 'Network error' }));
    throw error;
  }

  return res.json() as Promise<T>;
}

export async function getInstitution(): Promise<Institution> {
  return apiFetch<Institution>('/institution');
}

export async function getServices(): Promise<ApiResponse<Service[]>> {
  return apiFetch<ApiResponse<Service[]>>('/services');
}

export async function getServiceBySlug(slug: string): Promise<ApiResponse<Service>> {
  return apiFetch<ApiResponse<Service>>(`/services/${encodeURIComponent(slug)}`);
}

export async function createBooking(payload: BookingPayload): Promise<ApiResponse<QueueTicket>> {
  return apiFetch<ApiResponse<QueueTicket>>('/queue/booking', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export async function lookupTicket(
  ticketNumber: string,
  serviceDate: string,
): Promise<ApiResponse<QueueTicket>> {
  const params = new URLSearchParams({ ticket_number: ticketNumber, service_date: serviceDate });
  return apiFetch<ApiResponse<QueueTicket>>(`/queue/lookup?${params.toString()}`);
}

export async function getTicketDetail(ticketNumber: string): Promise<ApiResponse<QueueTicket>> {
  return apiFetch<ApiResponse<QueueTicket>>(`/queue/ticket/${encodeURIComponent(ticketNumber)}`);
}
