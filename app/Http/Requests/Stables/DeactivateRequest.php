<?php

namespace App\Http\Requests\Stables;

use Illuminate\Foundation\Http\FormRequest;

class DeactivateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $stable = $this->route('stable');

        if ($this->user()->can('deactivate', $stable)) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
