<?php

namespace App\Http\Controllers\Titles;

use App\Exceptions\CannotBeDeactivatedException;
use App\Models\Title;
use App\Http\Controllers\Controller;

class DeactivateController extends Controller
{
    /**
     * Deactivate a title.
     *
     * @param  \App\Models\Title  $title
     * @return \lluminate\Http\RedirectResponse
     */
    public function __invoke(Title $title)
    {
        $this->authorize('deactivate', $title);

        if (! $title->canBeDeactivated()) {
            throw new CannotBeDeactivatedException();
        }

        $title->deactivate();

        return redirect()->route('titles.index');
    }
}
