<?php

namespace App\Http\Requests\ComplaintRequests;

use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FormRequestComplaintAdmin extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'replyToComplaint' => $this->replyToComplaint(),
            default             => [],
        };
    }

    private function replyToComplaint(): array
    {
        return [
            'complaint_id' => ['required', 'integer', 'exists:complaints,id'],
            'admin_reply'  => ['required', 'string', 'min:3', 'max:2000'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
