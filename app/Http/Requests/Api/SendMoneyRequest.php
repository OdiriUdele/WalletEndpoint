<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendMoneyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => 'bail|required|double',
            'wallet_id' => 'required|integer|exists:wallet,wallet_id',
            'receiver_wallet' => 'required|integer|exists:wallet,wallet_id',
            'narration' => 'nullable|string'
        ];
    }
}
