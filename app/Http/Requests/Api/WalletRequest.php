<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalletRequest extends FormRequest
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
            'wallet_name'=>'required|string',
            'wallet_type_id' => ['required','integer',Rule::unique('wallets')->where(function ($query) {
                        return $query->where('user_id', auth()->user()->id);
                    }),
                    Rule::exists('wallet_types','id')
            ],
        ];
    }
}
