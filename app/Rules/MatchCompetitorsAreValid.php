<?php

namespace App\Rules;

use App\Models\MatchType;
use Illuminate\Contracts\Validation\Rule;

class MatchCompetitorsAreValid implements Rule
{
    public $message;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $matchType = MatchType::find($value['match_type_id']);

        if (!$matchType) {
            return true;
        }

        $sides = $value['competitors'];

        if (!$sides || !is_array($sides)) {
            return true;
        }

        if (count($sides) !== $matchType->number_of_sides) {
            $this->message = sprintf("Match requires %d sides, only %d provided", $matchType->number_of_sides, count($sides));
            return false;
        }

        $totalCompetitors = 0;

        foreach ($sides as $side) {
            if (!isset($side) || !is_array($side)) {
                return true;
            }

            if (array_key_exists('wrestlers', $side)) {
                $totalCompetitors += count($side);
            }

            if (array_key_exists('tagteams', $side)) {
                $totalCompetitors += count($side) * 2;
            }
        }

        if ($totalCompetitors !== $matchType->number_of_competitors) {
            $this->message = sprintf("Match requires %d competitors, only %d provided", $matchType->number_of_competitors, $totalCompetitors);
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
