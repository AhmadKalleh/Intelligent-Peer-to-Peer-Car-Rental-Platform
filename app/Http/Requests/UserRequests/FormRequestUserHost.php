<?php

namespace App\Http\Requests\UserRequests;

use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FormRequestUserHost extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'showHostDetails'    => $this->showHostDetails(),
            'changeHostPassword' => $this->changeHostPassword(),
            'updateProfileImage' => $this->updateProfileImage(),
            default              => [],
        };
    }

    private function showHostDetails(): array
    {
        return [
            'host_id' => ['required', 'integer', 'exists:hosts,id'],
        ];
    }

    private function changeHostPassword(): array
    {
        return [
            'user_id'      => ['required', 'integer', 'exists:users,id'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    private function updateProfileImage(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'image'   => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
