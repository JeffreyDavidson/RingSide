<?php

namespace Tests\Feature\SuperAdmin\Referees;

use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RefereeFactory;
use Tests\TestCase;

/**
 * @group referees
 * @group superadmins
 * @group roster
 */
class ViewRefereesListSuccessConditionsTest extends TestCase
{
    use RefreshDatabase;

    /** @var \Illuminate\Support\Collection */
    protected $referees;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $bookable = RefereeFactory::new()->count(3)->bookable()->create();
        $pendingEmployment = RefereeFactory::new()->count(3)->pendingEmployment()->create();
        $retired = RefereeFactory::new()->count(3)->retired()->create();
        $suspended = RefereeFactory::new()->count(3)->suspended()->create();
        $injured = RefereeFactory::new()->count(3)->injured()->create();

        $this->referees = collect([
            'bookable'           => $bookable,
            'pending-employment' => $pendingEmployment,
            'retired'            => $retired,
            'suspended'          => $suspended,
            'injured'            => $injured,
            'all'                => collect()
                                ->concat($bookable)
                                ->concat($pendingEmployment)
                                ->concat($retired)
                                ->concat($suspended)
                                ->concat($injured),
        ]);
    }

    /** @test */
    public function a_super_administrator_can_view_referees_page()
    {
        $this->actAs(Role::SUPER_ADMINISTRATOR);

        $response = $this->indexRequest('referees');

        $response->assertOk();
        $response->assertViewIs('referees.index');
    }

    /** @test */
    public function a_super_administrator_can_view_all_referees()
    {
        $this->actAs(Role::SUPER_ADMINISTRATOR);

        $responseAjax = $this->ajaxJson(route('referees.index'));

        $responseAjax->assertJson([
            'recordsTotal' => $this->referees->get('all')->count(),
            'data'         => $this->referees->get('all')->only(['id'])->toArray(),
        ]);
    }

    /** @test */
    public function a_super_administrator_can_view_bookable_referees()
    {
        $this->actAs(Role::SUPER_ADMINISTRATOR);

        $responseAjax = $this->ajaxJson(route('referees.index', ['status' => 'bookable']));

        $responseAjax->assertJson([
            'recordsTotal' => $this->referees->get('bookable')->count(),
            'data'         => $this->referees->get('bookable')->only(['id'])->toArray(),
        ]);
    }

    /** @test */
    public function a_super_administrator_can_view_pending_employment_referees()
    {
        $this->actAs(Role::SUPER_ADMINISTRATOR);

        $responseAjax = $this->ajaxJson(route('referees.index', ['status' => 'pending-employment']));

        $responseAjax->assertJson([
            'recordsTotal' => $this->referees->get('pending-employment')->count(),
            'data'         => $this->referees->get('pending-employment')->only(['id'])->toArray(),
        ]);
    }

    /** @test */
    public function a_super_administrator_can_view_retired_referees()
    {
        $this->actAs(Role::SUPER_ADMINISTRATOR);

        $responseAjax = $this->ajaxJson(route('referees.index', ['status' => 'retired']));

        $responseAjax->assertJson([
            'recordsTotal' => $this->referees->get('retired')->count(),
            'data'         => $this->referees->get('retired')->only(['id'])->toArray(),
        ]);
    }

    /** @test */
    public function a_super_administrator_can_view_suspended_referees()
    {
        $this->actAs(Role::SUPER_ADMINISTRATOR);

        $responseAjax = $this->ajaxJson(route('referees.index', ['status' => 'suspended']));

        $responseAjax->assertJson([
            'recordsTotal' => $this->referees->get('suspended')->count(),
            'data'         => $this->referees->get('suspended')->only(['id'])->toArray(),
        ]);
    }

    /** @test */
    public function a_super_administrator_can_view_injured_referees()
    {
        $this->actAs(Role::SUPER_ADMINISTRATOR);

        $responseAjax = $this->ajaxJson(route('referees.index', ['status' => 'injured']));

        $responseAjax->assertJson([
            'recordsTotal' => $this->referees->get('injured')->count(),
            'data'         => $this->referees->get('injured')->only(['id'])->toArray(),
        ]);
    }
}
