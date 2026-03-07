<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
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
        $serviceId = $this->route('service')?->id;

        return [
            'queue_pool_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('queue_pools', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('services', 'code')->ignore($serviceId),
            ],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'slug')->ignore($serviceId),
            ],
            'letter_code' => ['sometimes', 'nullable', 'string', 'max:3'],
            'description' => ['sometimes', 'nullable', 'string'],
            'requirements' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'booking_enabled' => ['sometimes', 'required', 'boolean'],
            'walk_in_enabled' => ['sometimes', 'required', 'boolean'],
            'daily_quota' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'sort_order' => ['sometimes', 'required', 'integer', 'min:0'],
        ];
    }
}
