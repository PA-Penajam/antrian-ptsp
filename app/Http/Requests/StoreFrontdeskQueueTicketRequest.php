<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFrontdeskQueueTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'channel' => ['required', 'in:assisted_same_day,walk_in_kiosk'],
            'service_date' => ['required', 'date', 'after_or_equal:today'],
            'visitor_name' => ['required', 'string', 'max:255'],
            'visitor_identifier' => ['nullable', 'string', 'max:64'],
            'visitor_phone' => ['nullable', 'string', 'max:30'],
            'visit_purpose' => ['nullable', 'string', 'in:pendaftaran,informasi_pengaduan,produk_hukum,ecourt'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $serviceId = $this->integer('service_id');
            $serviceDate = (string) $this->input('service_date');
            $channel = (string) $this->input('channel');

            $service = Service::query()->find($serviceId);
            if (! $service || ! $service->is_active) {
                $validator->errors()->add('service_id', 'Layanan tidak tersedia saat ini.');

                return;
            }

            if (in_array($channel, ['assisted_same_day', 'walk_in_kiosk'], true) && ! $service->walk_in_enabled) {
                $validator->errors()->add('service_id', 'Layanan ini tidak menerima antrean walk-in/frontdesk.');

                return;
            }

            if ($service->isQuotaFull($serviceDate)) {
                $validator->errors()->add('service_date', 'Kuota harian untuk layanan ini sudah penuh.');
            }
        });
    }

    /**
     * Get custom validation messages in Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'service_id.required' => 'Layanan wajib dipilih.',
            'service_id.exists' => 'Layanan yang dipilih tidak valid.',
            'channel.required' => 'Kanal wajib dipilih.',
            'channel.in' => 'Kanal yang dipilih tidak valid.',
            'service_date.required' => 'Tanggal layanan wajib diisi.',
            'service_date.date' => 'Format tanggal layanan tidak valid.',
            'service_date.after_or_equal' => 'Tanggal layanan tidak boleh di masa lalu.',
            'visitor_name.required' => 'Nama pengunjung wajib diisi.',
            'visitor_name.max' => 'Nama pengunjung maksimal 255 karakter.',
        ];
    }
}
