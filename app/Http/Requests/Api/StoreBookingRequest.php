<?php

namespace App\Http\Requests\Api;

use App\Enums\QueueStatus;
use App\Models\Service;
use App\Rules\WeekdayOnly;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'service_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:+14 days', new WeekdayOnly],
            'visitor_name' => ['required', 'string', 'max:255'],
            'visitor_identifier' => ['nullable', 'string', 'max:64'],
            'visitor_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $serviceId = $this->integer('service_id');
            $serviceDate = $this->input('service_date');

            $service = Service::query()->find($serviceId);

            if (! $service || ! $service->is_active) {
                $validator->errors()->add('service_id', 'Layanan tidak tersedia saat ini.');

                return;
            }

            if (! $service->booking_enabled) {
                $validator->errors()->add('service_id', 'Layanan ini tidak menerima pemesanan online.');

                return;
            }

            if ($service->daily_quota !== null) {
                $todayCount = \App\Models\QueueTicket::query()
                    ->where('service_id', $serviceId)
                    ->whereDate('service_date', $serviceDate)
                    ->whereNotIn('status', [QueueStatus::Cancelled])
                    ->count();

                if ($todayCount >= $service->daily_quota) {
                    $validator->errors()->add('service_date', 'Kuota harian untuk layanan ini sudah penuh.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Layanan harus dipilih.',
            'service_id.exists' => 'Layanan yang dipilih tidak valid.',
            'service_date.required' => 'Tanggal layanan harus diisi.',
            'service_date.date' => 'Format tanggal tidak valid.',
            'service_date.after_or_equal' => 'Tanggal layanan tidak boleh di masa lalu.',
            'service_date.before_or_equal' => 'Tanggal layanan maksimal 14 hari ke depan.',
            'visitor_name.required' => 'Nama pengunjung harus diisi.',
            'visitor_name.max' => 'Nama pengunjung maksimal 255 karakter.',
        ];
    }
}
