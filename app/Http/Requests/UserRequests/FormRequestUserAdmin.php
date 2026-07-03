<?php

namespace App\Http\Requests\UserRequests;

use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FormRequestUserAdmin extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'addUser'            => $this->addUser(),
            'promoteGuestToHost' => $this->userIdRequired(),
            'deleteGuest'        => $this->userIdRequired(),
            'deleteHost'         => $this->userIdRequired(),
            'toggleGuestStatus'  => $this->userIdRequired(),
            'updateProfileImage' => $this->updateProfileImage(),
            default              => [],
        };
    }

    private function addUser(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
            'role'      => ['nullable', 'string', 'in:guest,host,admin'],
        ];
    }

    private function userIdRequired(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
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
