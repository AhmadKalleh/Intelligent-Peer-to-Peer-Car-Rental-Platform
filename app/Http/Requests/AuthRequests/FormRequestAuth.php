<?php

namespace App\Http\Requests\AuthRequests;

use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FormRequestAuth extends FormRequest
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
        return match ($this->method()) {
            'POST' => match ($this->route()->getActionMethod()) {
                'register' => $this->register(),
                'verify_code' => $this->verifyCode(),
                'resend_code' => $this->resendCode(),
                'googleLogin' => $this->googleLogin(),
                'login' => $this->login(),
                default => []
            },
            default => []
        };
    }

    public function login(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6',
        ];
    }

    public function register(): array
    {
        return
        [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function verifyCode(): array
    {
        return
        [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ];
    }

    public function resendCode(): array
    {
        return
        [
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function googleLogin(): array
    {
        return
        [
            'id_token' => 'required|string',
        ];
    }


    protected function prepareForValidation()
    {

        if ($this->method() === 'POST'
        && $this->route()->getActionMethod() === 'register')
        {
                $this->merge([
                'full_name' => trim($this->full_name),
                'password' => trim($this->password),
            ]);
        }
    }


    protected function failedValidation(Validator $validator)
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
