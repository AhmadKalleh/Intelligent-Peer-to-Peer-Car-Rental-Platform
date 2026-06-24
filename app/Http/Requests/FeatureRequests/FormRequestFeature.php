<?php
// app/Http/Requests/FeatureRequests/FormRequestFeature.php

namespace App\Http\Requests\FeatureRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestFeature extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'store'   => $this->storeRules(),
            'update'  => $this->updateRules(),
            'destroy' => [],
            'index'   => [],
            default   => [],
        };
    }

    // ─── Store ───────────────────────────────────────────────────
    private function storeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:features,name'],
        ];
    }

    // ─── Update ──────────────────────────────────────────────────
    private function updateRules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:100', "unique:features,name,{$id}"],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
