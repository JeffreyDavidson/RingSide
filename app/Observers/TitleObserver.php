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
        } elseif ($title->isCurrentlyActivated()) {
            $title->status = TitleStatus::ACTIVE;
        } elseif ($title->isDeactivated()) {
            $title->status = TitleStatus::INACTIVE;
        } else {
            $title->status = TitleStatus::PENDING_ACTIVATION;
        }
    }
}
