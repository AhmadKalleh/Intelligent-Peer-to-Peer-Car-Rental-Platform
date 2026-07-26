<?php

namespace App\Http\Requests\VehicleRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Validation\Rule;

class FormRequestVehicleHost extends FormRequest
{
    use ResponseHelper;
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
        return match ($this->route()->getActionMethod()) {
            'store'         => $this->store(),
            'getHostVehicles' => $this->indexRules(),
            'showForHost'     => $this->showForHost(),
            'updateBasicInfo'     => $this->updateBasicInfoRules(),
            'updateListingStatus' => $this->updateListingStatusRules(),
            'storeSnooze'         => $this->snoozeRules(),
            'updatePricing'        => $this->updatePricingRules(),
            'storeCustomPricing'   => $this->storeCustomPricingRules(),
            'updateCustomPricing'  => $this->updateCustomPricingRules(),
            'destroyCustomPricing' => $this->destroyCustomPricingRules(),
            'uploadImages'    => $this->uploadImagesRules(),
            'destroyImage'    => $this->destroyImage(),
            'setPrimaryImage' => $this->destroyImage(),
            'syncFeatures' => $this->syncFeaturesRules(),
            'updateAvailability' => $this->updateAvailabilityRules(),
            'updateLocation' => $this->updateLocationRules(),
            default         => [],
        };
    }

    private function updateLocationRules(): array
    {
        return [
            'vehicle_id'         => ['required','exists:vehicles,id'],
            'city'           => ['required', 'string', 'max:100'],
            'pickup_address' => ['required', 'string'],
            'pickup_lat'     => ['required', 'numeric', 'between:-90,90'],
            'pickup_lng'     => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    private function updateAvailabilityRules(): array
    {
        return [
            'vehicle_id'         => ['required','exists:vehicles,id'],
            'available_from' => ['required', 'date', 'after_or_equal:today'],
            'available_to'   => ['required', 'date', 'after:available_from'],
        ];
    }

    private function syncFeaturesRules(): array
    {
        return [
            'vehicle_id'         => ['required','exists:vehicles,id'],
            'feature_ids'   => ['required', 'array', 'min:1'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ];
    }
    private function uploadImagesRules(): array
    {
        return [
            'vehicle_id'         => ['required','exists:vehicles,id'],
            'images'              => ['required', 'array', 'min:1', 'max:10'],
            'images.*'            => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'primary_image_index' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $images = $this->file('images', []);
            $index  = $this->input('primary_image_index');

            if (!is_null($index) && $index >= count($images)) {
                $validator->errors()->add(
                    'primary_image_index',
                    'The primary image index is out of bounds.'
                );
            }
        });
    }

    private function destroyImage(): array
    {
        return [
            'vehicle_id'         => ['required','exists:vehicles,id'],
            'image_id'              => ['required', 'exists:images,id'],
        ];
    }

    private function updatePricingRules(): array
    {
        return [
            'vehicle_id'         => ['required','exists:vehicles,id'],
            'base_price_per_day' => ['required', 'numeric', 'min:1'],
            'delivery_available' => ['required', 'boolean'],
            'delivery_fee'       => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:delivery_available,true',
            ],
        ];
    }

    private function destroyCustomPricingRules(): array
    {
        return [
            'vehicle_id'         => ['required','exists:vehicles,id'],
            'custome_pricing_id' => ['required','exists:vehicle_custom_pricings,id'],
        ];
    }

    private function updateCustomPricingRules(): array
    {
        return [
            'vehicle_id'         => ['required','exists:vehicles,id'],
            'custome_pricing_id' => ['required','exists:vehicle_custom_pricings,id'],
            'date_from'     => ['sometimes', 'date', 'after_or_equal:today'],
            'date_to'       => ['sometimes', 'date', 'after:date_from'],
            'price_per_day' => ['sometimes', 'numeric', 'min:1'],
            'reason'        => ['nullable', 'string', 'max:255'],
        ];
    }

    private function storeCustomPricingRules(): array
    {
        return [
            'vehicle_id'         => ['required','exists:vehicles,id'],
            'date_from'     => ['required', 'date', 'after_or_equal:today'],
            'date_to'       => ['required', 'date', 'after:date_from'],
            'price_per_day' => ['required', 'numeric', 'min:1'],
            'reason'        => ['nullable', 'string', 'max:255'],
        ];
    }

    private function updateListingStatusRules(): array
    {
        return [
            'listing_status' => ['required', 'in:listed,unlisted'],
            'vehicle_id'         => ['required','exists:vehicles,id'],
        ];
    }

    private function snoozeRules(): array
    {
        return [
            'vehicle_id'         => ['required','exists:vehicles,id'],
            'snoozed_from'  => ['required', 'date', 'after_or_equal:today'],
            'snoozed_until' => ['required', 'date', 'after:snoozed_from'],
        ];
    }

    private function updateBasicInfoRules(): array
    {
        return [
            'vehicle_id'         => ['required','exists:vehicles,id'],
            'make'               => ['sometimes', 'string', 'max:100'],
            'model'              => ['sometimes', 'string', 'max:100'],
            'year'               => ['sometimes', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'color'              => ['nullable', 'string', 'max:50'],
            'fuel_type'          => ['sometimes', 'in:petrol,diesel,electric,hybrid'],
            'transmission'       => ['sometimes', 'in:automatic,manual'],
            'engine_capacity'    => ['nullable', 'numeric', 'min:0.1', 'max:999.9'],
            'seats'              => ['sometimes', 'integer', 'min:1', 'max:20'],
            'plate_number'       => [
                'sometimes',
                'string',
                'max:30',
                // نستثني السيارة الحالية من الـ unique
                Rule::unique('vehicles', 'plate_number')->ignore($this->input('vehicle_id')),
            ],
            'guest_instructions' => ['nullable', 'string'],
        ];
    }

    private function store(): array
    {
        $rules = [
            'is_first_time'      => ['required', 'boolean'],

            // معلومات السيارة
            'make'               => ['required', 'string', 'max:100'],
            'model'              => ['required', 'string', 'max:100'],
            'year'               => ['required', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'color'              => ['nullable', 'string', 'max:50'],
            'fuel_type'          => ['required', 'in:petrol,diesel,electric,hybrid'],
            'transmission'       => ['required', 'in:automatic,manual'],
            'engine_capacity'    => ['nullable', 'numeric', 'min:0.1', 'max:999.9'],
            'seats'              => ['required', 'integer', 'min:1', 'max:20'],
            'plate_number'       => ['required', 'string', 'max:30', 'unique:vehicles,plate_number'],

            // دفتر الميكانيك (مطلوب دائماً)
            'mechanic_booklet'   => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // التسعير
            'base_price_per_day' => ['required', 'numeric', 'min:1'],

            // التوصيل
            'delivery_available' => ['nullable', 'boolean'],
            'delivery_fee'       => ['nullable', 'numeric', 'min:0', 'required_if:delivery_available,true'],

            // الموقع
            'pickup_address'     => ['nullable', 'string'],
            'pickup_lat'         => ['nullable', 'numeric', 'between:-90,90'],
            'pickup_lng'         => ['nullable', 'numeric', 'between:-180,180'],
            'city'               => ['nullable', 'string', 'max:100'],

            // تعليمات
            'guest_instructions' => ['nullable', 'string'],

            // المميزات
            'features'           => ['required', 'array'],
            'features.*'         => ['integer', 'max:100'],

            // الصور
            'images'             => ['required', 'array', 'min:1', 'max:10'],
            'images.*'           => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'primary_image_index'=> ['nullable', 'integer', 'min:0'],

            // التوفر
            'available_from'     => ['nullable', 'date', 'after_or_equal:today'],
            'available_to'       => ['nullable', 'date', 'after:available_from'],
        ];

        // شهادة القيادة فقط عند أول مرة
        if ($this->boolean('is_first_time')) {
            $rules['driving_license'] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];
        }

        return $rules;
    }

    private function showForHost():array
    {
        return [
            'vehicle_id' =>['required','exists:vehicles,id']
        ];
    }

    private function indexRules(): array
    {
        return [
            'status'   => ['required', 'in:all,approved,rejected,pending'],
            //'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
