<?php

namespace App\Http\Controllers;

use App\TagTeam;
use App\Http\Requests\StoreTagTeamRequest;
use App\Http\Requests\UpdateTagTeamRequest;

class TagTeamsController extends Controller
{
    /**
     * Show the form for creating a new tag team.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', TagTeam::class);

        return response()->view('tagteams.create');
    }

    /**
     * Create a new tag team.
     *
     * @param  \App\Http\Requests\StoreTagTeamRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreTagTeamRequest $request)
    {
        $tagteam = TagTeam::create($request->except('wrestlers'));

        $tagteam->addWrestlers($request->input('wrestlers'));

        return redirect()->route('tagteams.index');
    }

    /**
     * Show the form for editing a tag team.
     *
     * @param  \App\TagTeam  $tagteam
     * @return \lluminate\Http\Response
     */
    public function edit(TagTeam $tagteam)
    {
        $this->authorize('update', TagTeam::class);

        return response()->view('tagteams.edit', compact('tagteam'));
    }

    /**
     * Update a given tag team.
     *
     * @param  \App\Http\Requests\UpdateTagTeamRequest  $request
     * @param  \App\TagTeam  $tagteam
     * @return \lluminate\Http\RedirectResponse
     */
    public function update(UpdateTagTeamRequest $request, TagTeam $tagteam)
    {
        $tagteam->update($request->except('wrestlers'));

        $tagteam->wrestlers()->sync($request->input('wrestlers'));

        return redirect()->route('tagteams.index');
    }
}
