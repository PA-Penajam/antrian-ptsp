<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicQueueBookingRequest extends FormRequest
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
            'service_date' => ['required', 'date'],
            'visitor_name' => ['required', 'string', 'max:255'],
            'visitor_identifier' => ['required', 'string', 'max:64'],
            'visitor_phone' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'visitor_identifier.required' => 'Nomor KTP/identitas wajib diisi agar tiket dapat ditelusuri kembali.',
            'visitor_phone.required' => 'Nomor telepon/WhatsApp wajib diisi agar petugas dapat menghubungi Anda.',
        ];
    }
}
