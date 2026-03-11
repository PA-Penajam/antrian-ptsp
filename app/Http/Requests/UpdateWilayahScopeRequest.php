<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWilayahScopeRequest extends FormRequest
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
            'kabupaten_kode' => [
                'required',
                'string',
                'size:5',
                'regex:/^\d{2}\.\d{2}$/',
                Rule::exists('wilayah', 'kode'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kabupaten_kode.required' => 'Kabupaten wajib dipilih.',
            'kabupaten_kode.size' => 'Format kode kabupaten tidak valid.',
            'kabupaten_kode.regex' => 'Format kode kabupaten tidak valid.',
            'kabupaten_kode.exists' => 'Kabupaten yang dipilih tidak ditemukan.',
        ];
    }
}
