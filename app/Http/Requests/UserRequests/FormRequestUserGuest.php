<?php

namespace App\Http\Requests\UserRequests;

use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FormRequestUserGuest extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'updateProfileImage'  => $this->updateProfileImage(),
            'showGuestDetails'    => $this->showGuestDetails(),
            'changeGuestPassword' => $this->changeGuestPassword(),
            default               => [],
        };
    }

    private function updateProfileImage(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    private function showGuestDetails(): array
    {
        return [];
    }

    private function changeGuestPassword(): array
    {
        return [
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
