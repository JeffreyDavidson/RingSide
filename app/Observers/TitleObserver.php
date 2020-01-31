<?php

namespace App\Observers;

use App\Models\Title;
use App\Enums\TitleStatus;

class TitleObserver
{
    /**
     * Handle the Title "saving" event.
     *
     * @param  App\Models\Title  $title
     * @return void
     */
    public function saving(Title $title)
    {
        if ($title->isRetired()) {
            $title->status = TitleStatus::RETIRED;
        } elseif ($title->isCompetable()) {
            $title->status = TitleStatus::COMPETABLE;
        } else {
            $title->status = TitleStatus::PENDING_INTRODUCTION;
        }
    }
}
