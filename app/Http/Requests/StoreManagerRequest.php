<?php

namespace App\Http\Requests;

use App\Manager;
use Illuminate\Foundation\Http\FormRequest;

class StoreManagerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Manager::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'hired_at' => ['required', 'date_format:Y-m-d H:i:s']
        ];
    }
}
