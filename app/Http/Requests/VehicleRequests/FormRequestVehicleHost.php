<?php

namespace App\Http\Requests\VehicleRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

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
            default         => [],
        };
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
