<?php

namespace Tests\Feature\Admin\Titles;

use Tests\TestCase;
use App\Models\Title;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group titles
 * @group admins
 */
class ViewTitlesListSuccessConditionsTest extends TestCase
{
    use RefreshDatabase;

    /** @var \Illuminate\Support\Collection */
    protected $titles;

    protected function setUp(): void
    {
        parent::setUp();

        $bookable            = factory(Title::class, 3)->states('bookable')->create();
        $pendingIntroduction = factory(Title::class, 3)->states('pending-introduction')->create();
        $retired             = factory(Title::class, 3)->states('retired')->create();

        $this->titles = collect([
            'bookable'             => $bookable,
            'pending-introduction' => $pendingIntroduction,
            'retired'              => $retired,
            'all'                  => collect()
                                ->concat($bookable)
                                ->concat($pendingIntroduction)
                                ->concat($retired)
        ]);
    }

    /** @test */
    public function an_administrator_can_view_titles_page()
    {
        $this->actAs('administrator');

        $response = $this->get(route('titles.index'));

        $response->assertOk();
        $response->assertViewIs('titles.index');
    }

    /** @test */
    public function an_administrator_can_view_all_titles()
    {
        $this->actAs('administrator');

        $responseAjax = $this->ajaxJson(route('titles.index'));

        $responseAjax->assertJson([
            'recordsTotal' => $this->titles->get('all')->count(),
            'data'         => $this->titles->get('all')->only(['id'])->toArray(),
        ]);
    }

    /** @test */
    public function an_administrator_can_view_all_bookable_titles()
    {
        $this->actAs('administrator');

        $responseAjax = $this->ajaxJson(route('titles.index', ['status' => 'bookable']));

        $responseAjax->assertJson([
            'recordsTotal' => $this->titles->get('bookable')->count(),
            'data'         => $this->titles->get('bookable')->only(['id'])->toArray(),
        ]);
    }

    /** @test */
    public function an_administrator_can_view_all_pending_introduction_titles()
    {
        $this->actAs('administrator');
        $responseAjax = $this->ajaxJson(route('titles.index', ['status' => 'pending-introduction']));

        $responseAjax->assertJson([
            'recordsTotal' => $this->titles->get('pending-introduction')->count(),
            'data'         => $this->titles->get('pending-introduction')->only(['id'])->toArray(),
        ]);
    }

    /** @test */
    public function an_administrator_can_view_all_retired_titles()
    {
        $this->actAs('administrator');
        $responseAjax = $this->ajaxJson(route('titles.index', ['status' => 'retired']));

        $responseAjax->assertJson([
            'recordsTotal' => $this->titles->get('retired')->count(),
            'data'         => $this->titles->get('retired')->only(['id'])->toArray(),
        ]);
    }
}
