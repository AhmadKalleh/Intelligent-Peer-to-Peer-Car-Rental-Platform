<?php

namespace App\Http\Requests\VehicleRequests;

use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FormRequestVehicle extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->method()) {
            'GET'           => $this->index(),
            'POST'          => $this->store(),
            'PUT', 'PATCH'  => $this->update(),
            default         => [],
        };
    }

    // =====================
    //     قواعد القائمة (GET)
    // =====================

    private function index(): array
    {
        return [
            'brand'        => 'sometimes|string|max:100',
            'status'       => 'sometimes|in:available,unavailable,rented,maintenance',
            'transmission' => 'sometimes|in:automatic,manual',
            'fuel_type'    => 'sometimes|in:petrol,diesel,electric,hybrid',
            'min_price'    => 'sometimes|numeric|min:0',
            'max_price'    => 'sometimes|numeric|min:0|gte:min_price',
            'per_page'     => 'sometimes|integer|min:1|max:100',
        ];
    }

    // =====================
    //     قواعد الإضافة (POST)
    // =====================

    private function store(): array
    {
        return [
            'host_id'       => 'required|integer|exists:hosts,id',
            'brand'         => 'required|string|max:100',
            'model'         => 'required|string|max:100',
            'year'          => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'color'         => 'required|string|max:50',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate',
            'transmission'  => 'required|in:automatic,manual',
            'fuel_type'     => 'required|in:petrol,diesel,electric,hybrid',
            'seats'         => 'required|integer|min:1|max:20',
            'daily_price'   => 'required|numeric|min:0',
            'description'   => 'nullable|string|max:1000',
            'location'      => 'required|string|max:255',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',

            // الصور
            'images'            => 'nullable|array|max:10',
            'images.*'          => 'image|mimes:jpeg,png,jpg,webp|max:5120',

            // الميزات
            'features'          => 'nullable|array',
            'features.*.name'   => 'required_with:features|string|max:100',
            'features.*.value'  => 'nullable|string|max:255',

            // التوافر
            'availabilities'                  => 'nullable|array',
            'availabilities.*.available_from' => 'required_with:availabilities|date|after_or_equal:today',
            'availabilities.*.available_to'   => 'required_with:availabilities|date|after:availabilities.*.available_from',
            'availabilities.*.is_blocked'     => 'nullable|boolean',
            'availabilities.*.note'           => 'nullable|string|max:255',

            // التسعير المخصص
            'custom_pricings'                => 'nullable|array',
            'custom_pricings.*.date_from'    => 'required_with:custom_pricings|date|after_or_equal:today',
            'custom_pricings.*.date_to'      => 'required_with:custom_pricings|date|after:custom_pricings.*.date_from',
            'custom_pricings.*.custom_price' => 'required_with:custom_pricings|numeric|min:0',
            'custom_pricings.*.reason'       => 'nullable|string|max:255',
        ];
    }

    // =====================
    //     قواعد التعديل (PUT/PATCH)
    // =====================

    private function update(): array
    {
        $vehicleId = $this->route('vehicle');

        return [
            'brand'         => 'sometimes|string|max:100',
            'model'         => 'sometimes|string|max:100',
            'year'          => 'sometimes|integer|min:1990|max:' . (date('Y') + 1),
            'color'         => 'sometimes|string|max:50',
            'license_plate' => 'sometimes|string|max:20|unique:vehicles,license_plate,' . $vehicleId,
            'transmission'  => 'sometimes|in:automatic,manual',
            'fuel_type'     => 'sometimes|in:petrol,diesel,electric,hybrid',
            'seats'         => 'sometimes|integer|min:1|max:20',
            'daily_price'   => 'sometimes|numeric|min:0',
            'description'   => 'nullable|string|max:1000',
            'location'      => 'sometimes|string|max:255',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'status'        => 'sometimes|in:available,unavailable,rented,maintenance',

            // الصور الجديدة
            'images'            => 'nullable|array|max:10',
            'images.*'          => 'image|mimes:jpeg,png,jpg,webp|max:5120',

            // الميزات
            'features'          => 'nullable|array',
            'features.*.name'   => 'required_with:features|string|max:100',
            'features.*.value'  => 'nullable|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation(
                [],
                $validator->errors()->first(),
                422
            )
        );
    }
}
