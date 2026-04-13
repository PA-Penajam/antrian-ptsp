<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $poolId = $this->route('pool')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('queue_pools', 'code')->ignore($poolId),
            ],
            'letter_code' => [
                'sometimes',
                'required',
                'string',
                'max:5',
                Rule::unique('queue_pools', 'letter_code')->ignore($poolId),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'required', 'boolean'],
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
