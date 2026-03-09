export interface Service {
  id: number;
  name: string;
  code: string;
  slug: string;
  description: string | null;
  requirements: string | null;
  booking_enabled: boolean;
  daily_quota: number | null;
  remaining_quota: number | null;
}

export interface QueueTicket {
  ticket_number: string;
  service_date: string;
  visitor_name: string;
  status: string;
  status_label: string;
  service: Service;
  queue_position: number | null;
  counter_name: string | null;
  checked_in_at: string | null;
  called_at: string | null;
  completed_at: string | null;
  cancelled_at: string | null;
}

export interface Institution {
  name: string;
  address: string;
  phone: string;
  operating_hours: string;
  logo_path: string;
}

export interface BookingPayload {
  service_id: number;
  service_date: string;
  visitor_name: string;
  visitor_identifier?: string;
  visitor_phone?: string;
  notes?: string;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

export interface ApiResponse<T> {
  data: T;
}
