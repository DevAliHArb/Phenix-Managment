<?php

namespace App\Http\Requests\PurchasedOrder;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class PurchasedOrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // return Auth::check();
        return true;
    }


    public function rules(): array
    {
        return [
            '*.title' => 'required|string',
            '*.description' => 'required|string',
            '*.isbn' => 'required|string',
            '*.quantity' => 'required|integer|min:1',
        ];
    }
}
