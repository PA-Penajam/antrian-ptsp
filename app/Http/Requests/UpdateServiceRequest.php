<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $slug = $this->input('slug');

        if ($slug === '' && ! empty($name)) {
            $this->merge([
                'slug' => Str::slug($name),
            ]);
        }
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
            'description' => ['sometimes', 'nullable', 'string'],
            'requirements' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'booking_enabled' => ['sometimes', 'required', 'boolean'],
            'walk_in_enabled' => ['sometimes', 'required', 'boolean'],
            'daily_quota' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'sort_order' => ['sometimes', 'required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'queue_pool_id.required' => 'Pool antrian wajib dipilih.',
            'queue_pool_id.exists' => 'Pool antrian yang dipilih tidak aktif atau tidak ditemukan.',
            'name.required' => 'Nama layanan wajib diisi.',
            'code.required' => 'Kode layanan wajib diisi.',
            'code.unique' => 'Kode layanan sudah digunakan.',
            'slug.required' => 'Slug wajib diisi.',
            'slug.unique' => 'Slug sudah digunakan.',
            'sort_order.required' => 'Urutan wajib diisi.',
            'daily_quota.integer' => 'Kuota harian harus berupa angka.',
            'daily_quota.min' => 'Kuota harian minimal 1.',
        ];
    }
}
