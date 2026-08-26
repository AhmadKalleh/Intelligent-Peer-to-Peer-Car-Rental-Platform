<?php

namespace App\Http\Requests\VehicleRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestVehicleAdmin extends FormRequest
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
            'approve'       => $this->approve(),
            'reject'        => $this->reject(),
            'showPending'   => $this->approve(),
            default         => [],
        };
    }

    private function approve():array
    {
        return [
            'vehicle_id' =>['required','exists:vehicles,id']
        ];
    }



    private function reject(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
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
