<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            "first_name" => "bail|required|min:3|max:15|regex:/^[a-zA-Z]+$/u",
            "last_name" => "bail|required|min:3|max:15|regex:/^[a-zA-Z]+$/u",
            "email" => "bail|required|unique:users|email",
            "password" => "bail|required|min:6|confirmed",
        ];
    }
}
