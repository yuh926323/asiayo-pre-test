<?php

namespace App\Http\Requests\Orders;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FormatOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'required|string',
            'name' => 'required|string',
            'address.city' => 'required|string',
            'address.district' => 'required|string',
            'address.street' => 'required|string',
            'price' => 'required|numeric',
            'currency' => 'required',
        ];
    }

    /**
     * 處理驗證錯誤的訊息
     *
     * @param  mixed $validator
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors();
        $errorMessage = [];
        foreach ($errors->getMessages() as $messages) {
            $errorMessage = array_merge($errorMessage, $messages);
        }
        $errorMessage = implode(',', $errorMessage);
        throw new HttpResponseException(response()->json([
            'errorMessage' => $errorMessage,
        ], 422));
    }
}
