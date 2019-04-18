<?php

namespace App\Http\Requests;

use Illuminate\Validation\Validator;
use App\Rules\CorrectMatchSidesCount;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MatchCompetitorsAreValid;

class StoreMatchesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $event = $this->route('event');

        return $this->user()->can('addMatches', $event);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $event = $this->route('event');
        $date = $event->date->toDateTimeString();

        return [
            'matches' => ['required', 'array', 'min:1'],
            'matches.*' => ['required', 'array'],
            'matches.*.match_type_id' => ['bail', 'required', 'integer', 'exists:match_types,id'],
            'matches.*.competitors' => ['required', 'array'],
            'matches.*.competitors.*.wrestlers' => ['sometimes', 'array'],
            'matches.*.competitors.*.wrestlers.*' => ['required_with:matches.*.competitors.*.wrestlers', 'integer', 'exists:wrestlers,id'],
            'matches.*.competitors.*.tagteams' => ['sometimes', 'array'],
            'matches.*.competitors.*.tagteams.*' => ['required_with:matches.*.competitors.*.tagteams', 'integer', 'exists:tagteams,id'],
            'matches.*.preview' => ['required', 'string'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->addRules([
            'matches.*' => [
                new CorrectMatchSidesCount,
                new MatchCompetitorsAreValid,
            ],
        ]);
    }
}
