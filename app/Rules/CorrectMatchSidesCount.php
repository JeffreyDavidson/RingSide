<?php

namespace App\Rules;

use App\Models\MatchType;
use Illuminate\Contracts\Validation\Rule;

class CorrectMatchSidesCount implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $type = MatchType::find($value['match_type_id']);
        
        // Match type was not found; pretend it succeeded
        if (!$type) {
            return true;
        }

        return count($value['competitors']) == $type->number_of_sides;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Incorrect number of sides.';
    }
}
