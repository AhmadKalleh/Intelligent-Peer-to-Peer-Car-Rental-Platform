<?php
// app/Http/Requests/VehicleRequests/FormRequestVehicle.php

namespace App\Http\Requests\VehicleRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestVehiclePublic extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'nearby'        => $this->nearbyRules(),
            'resetLocation' => [],
            'all'           => [],
            'cities'        => [],
            'delivery'      => [],
            'airports'      => [],
            'show'          => $this->show(),
            default         => [],
        };
    }



    private function nearbyRules(): array
    {
        return [
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    private function show():array
    {
        return [
            'vehicle_id' =>['required','exists:vehicles,id']
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
