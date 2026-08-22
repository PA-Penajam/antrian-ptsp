<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckInQueueTicketRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'ticket_number' => strtoupper(trim((string) $this->input('ticket_number'))),
        ]);
    }

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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ticket_number' => ['required', 'string', 'max:50', 'exists:queue_tickets,ticket_number'],
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_number.required' => 'Nomor antrian wajib diisi.',
            'ticket_number.exists' => 'Nomor antrian tidak ditemukan.',
        ];
    }
}
