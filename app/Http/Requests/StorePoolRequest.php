<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:queue_pools,code'],
            'letter_code' => ['required', 'string', 'max:5', 'unique:queue_pools,letter_code'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'letter_code.unique' => 'Kode huruf antrian sudah digunakan oleh pool lain.',
            'code.unique' => 'Kode pool sudah digunakan.',
        ];
    }
}
