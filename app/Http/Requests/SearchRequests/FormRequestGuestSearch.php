<?php
// app/Http/Requests/SearchRequests/FormRequestGuestSearch.php

namespace App\Http\Requests\SearchRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestGuestSearch extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'search' => $this->searchRules(),
            'filter' => $this->filterRules(),
            default  => [],
        };
    }

    // ─── Search ───────────────────────────────────────────────
    private function searchRules(): array
    {
        return [
            'search_type' => ['required', 'in:location,anywhere'],

            // مطلوب لكل الأنواع ماعدا anywhere
            'lat'         => [
                'required_unless:search_type,anywhere',
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'lng'         => [
                'required_unless:search_type,anywhere',
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            // radius اختياري
            'radius'      => ['required_unless:search_type,anywhere','nullable', 'integer', 'min:1', 'max:100'],

            // التواريخ
            'date_from'   => ['nullable', 'date', 'after_or_equal:today'],
            'date_to'     => ['nullable', 'date', 'after:date_from'],

            // Cursor Pagination
            'cursor'      => ['nullable', 'string'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    // ─── Filter ───────────────────────────────────────────────
    private function filterRules(): array
    {
        return [
            // السعر
            'min_price'          => ['nullable', 'numeric', 'min:0'],
            'max_price'          => ['nullable', 'numeric', 'min:0', 'gt:min_price'],

            // السيارة
            'make'               => ['nullable', 'string', 'max:100'],
            'model'              => ['nullable', 'string', 'max:100'],
            'year_from'          => ['nullable', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'year_to'            => ['nullable', 'integer', 'min:1990', 'max:' . (date('Y') + 1), 'gte:year_from'],
            'fuel_type'          => ['nullable', 'in:petrol,diesel,electric,hybrid'],
            'transmission'       => ['nullable', 'in:automatic,manual'],
            'seats'              => ['nullable', 'integer', 'min:1', 'max:20'],
            'engine_capacity'    => ['nullable', 'numeric', 'min:0.1'],

            // الميزات
            'feature_ids'        => ['nullable', 'array'],
            'feature_ids.*'      => ['integer', 'exists:features,id'],

            // التوصيل
            'delivery_available' => ['nullable', 'boolean'],

            // التقييم
            'min_rating'         => ['nullable', 'numeric', 'min:0', 'max:5'],

            // All-Star Host
            'all_star_host'      => ['nullable', 'boolean'],

            // الترتيب
            'sort_by'            => ['nullable', 'in:price_asc,price_desc,rating_desc,bookings_desc'],

            // Cursor Pagination
            'cursor'             => ['nullable', 'string'],
            'per_page'           => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
