<?php

namespace Tests\Feature\Admin\TagTeams;

use App\Models\TagTeam;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group tagteams
 * @group admins
 */
class ViewTagTeamsListSuccessConditionsTest extends TestCase
{
    use RefreshDatabase;

    /** @var \Illuminate\Support\Collection */
    protected $tagteams;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $bookable            = factory(TagTeam::class, 3)->states('bookable')->create();
        $pendingEmployment   = factory(TagTeam::class, 3)->states('pending-employment')->create();
        $retired             = factory(TagTeam::class, 3)->states('retired')->create();
        $suspended           = factory(TagTeam::class, 3)->states('suspended')->create();

        $this->tagteams = collect([
            'bookable'             => $bookable,
            'pending-employment'   => $pendingEmployment,
            'retired'              => $retired,
            'suspended'            => $suspended,
            'all'                  => collect()
                                ->concat($bookable)
                                ->concat($pendingEmployment)
                                ->concat($retired)
                                ->concat($suspended)
        ]);
    }

    /** @test */
    public function an_administrator_can_view_tag_teams_page()
    {
        $this->actAs('administrator');

        $response = $this->get(route('tagteams.index'));

        $response->assertOk();
        $response->assertViewIs('tagteams.index');
    }

    /** @test */
    public function an_administrator_can_view_all_tag_teams()
    {
        $this->actAs('administrator');

        $responseAjax = $this->ajaxJson(route('tagteams.index'));

        $responseAjax->assertJson([
            'recordsTotal' => $this->tagteams->get('all')->count(),
            'data'         => $this->tagteams->get('all')->only(['id'])->toArray(),
        ]);
    }

    /** @test */
    public function an_administrator_can_view_bookable_tag_teams()
    {
        $this->actAs('administrator');

        $responseAjax = $this->ajaxJson(route('tagteams.index', ['status' => 'bookable']));

        $responseAjax->assertJson([
            'recordsTotal' => $this->tagteams->get('bookable')->count(),
            'data'         => $this->tagteams->get('bookable')->only(['id'])->toArray(),
        ]);
    }

    /** @test */
    public function an_administrator_can_view_pending_employment_tag_teams()
    {
        $this->actAs('administrator');

        $responseAjax = $this->ajaxJson(route('tagteams.index', ['status' => 'pending-employment']));

        $responseAjax->assertJson([
            'recordsTotal' => $this->tagteams->get('pending-employment')->count(),
            'data'         => $this->tagteams->get('pending-employment')->only(['id'])->toArray(),
        ]);
    }

    /** @test */
    public function an_administrator_can_view_retired_tag_teams()
    {
        $this->actAs('administrator');

        $responseAjax = $this->ajaxJson(route('tagteams.index', ['status' => 'retired']));

        $responseAjax->assertJson([
            'recordsTotal' => $this->tagteams->get('retired')->count(),
            'data'         => $this->tagteams->get('retired')->only(['id'])->toArray(),
        ]);
    }

    /** @test */
    public function an_administrator_can_view_suspended_tag_teams()
    {
        $this->actAs('administrator');

        $responseAjax = $this->ajaxJson(route('tagteams.index', ['status' => 'suspended']));

        $responseAjax->assertJson([
            'recordsTotal' => $this->tagteams->get('suspended')->count(),
            'data'         => $this->tagteams->get('suspended')->only(['id'])->toArray(),
        ]);
    }
}
