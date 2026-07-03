<?php

namespace App\Http\Requests\FavoriteRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestFavorite extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        if (! $this->user()->hasRole('guest')) {
            throw new HttpResponseException(
                $this->Error([], 'Unauthorized. Only guests can access favorites.', 403)
            );
        }

        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'createList'  => ['name'     => ['required', 'string', 'max:100']],
            'renameList'  => [
                'list_id' => ['required', 'integer', 'exists:favorite_lists,id'],
                'name'    => ['required', 'string',  'max:100'],
            ],
            'toggle'      => [
                'vehicle_id'       => ['required', 'integer', 'exists:vehicles,id'],
                'favorite_list_id' => ['required', 'integer', 'exists:favorite_lists,id'],
            ],
            'move'        => [
                'vehicle_id'   => ['required', 'integer', 'exists:vehicles,id'],
                'from_list_id' => ['required', 'integer', 'exists:favorite_lists,id'],
                'to_list_id'   => ['required', 'integer', 'exists:favorite_lists,id', 'different:from_list_id'],
            ],
            'getList',
            'deleteList'  => ['list_id'    => ['required', 'integer', 'exists:favorite_lists,id']],
            'heartStatus' => ['vehicle_id' => ['required', 'integer', 'exists:vehicles,id']],
            default       => [],
        };
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
