<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LookupTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_number' => ['required', 'string'],
            'service_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_number.required' => 'Nomor tiket wajib diisi',
            'ticket_number.string' => 'Nomor tiket harus berupa teks',
            'service_date.required' => 'Tanggal layanan wajib diisi',
            'service_date.date' => 'Tanggal layanan harus format tanggal yang valid',
        ];
    }
}
