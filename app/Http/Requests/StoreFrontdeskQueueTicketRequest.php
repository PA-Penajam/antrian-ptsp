<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'service_date' => ['required', 'date'],
            'visitor_name' => ['required', 'string', 'max:255'],
            'visitor_identifier' => ['nullable', 'string', 'max:64'],
            'visitor_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
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
            'visitor_name.required' => 'Nama pengunjung wajib diisi.',
            'visitor_name.max' => 'Nama pengunjung maksimal 255 karakter.',
        ];
    }
}
