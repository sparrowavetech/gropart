<?php

namespace Botble\ProductBundles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,mix'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],

            'pricing_type' => ['required', 'in:fixed_total,percent_off,amount_off'],
            'pricing_value' => ['required', 'numeric'],

            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],

            // Select2 multi-select returns an array of product IDs.
            // We keep backward compatibility by normalizing a comma-separated string to an array in prepareForValidation().
            'attached_product_ids' => ['nullable', 'array'],
            'attached_product_ids.*' => ['integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $attached = $this->input('attached_product_ids');

        if (is_string($attached)) {
            $attached = collect(explode(',', $attached))
                ->map(fn ($v) => (int) trim($v))
                ->filter(fn ($v) => $v > 0)
                ->values()
                ->all();
        }

        $this->merge([
            'is_active' => (bool) $this->input('is_active', false),
            'is_featured' => (bool) $this->input('is_featured', false),
            'attached_product_ids' => is_array($attached) ? array_values(array_filter(array_map('intval', $attached))) : [],
        ]);
    }
}
