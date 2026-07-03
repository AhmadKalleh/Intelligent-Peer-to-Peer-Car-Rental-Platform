<?php

namespace App\Http\Requests\ComplaintRequests;

use App\Services\Complaint\ComplaintReasonService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class FormRequestComplaintHost extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'submitComplaint' => $this->submitComplaint(),
            default            => [],
        };
    }

    private function submitComplaint(): array
    {
        return [
            'reported_user_id' => ['required', 'integer', 'exists:users,id'],
            'reason_id'        => ['required', 'integer', Rule::in(ComplaintReasonService::ids())],
            'details'          => ['nullable', 'string', 'max:1000', 'required_if:reason_id,' . ComplaintReasonService::otherId()],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
