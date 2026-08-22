<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $slug = $this->input('slug');

        if (empty($slug) && ! empty($name)) {
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
        return [
            'queue_pool_id' => [
                'required',
                'integer',
                Rule::exists('queue_pools', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:services,code'],
            'slug' => ['required', 'string', 'max:255', 'unique:services,slug'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'booking_enabled' => ['required', 'boolean'],
            'walk_in_enabled' => ['required', 'boolean'],
            'daily_quota' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['required', 'integer', 'min:0'],
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
