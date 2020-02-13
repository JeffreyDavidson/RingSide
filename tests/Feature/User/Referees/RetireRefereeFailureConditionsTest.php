<?php

namespace Tests\Feature\User\Referees;

use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RefereeFactory;
use Tests\TestCase;

/**
 * @group referees
 * @group users
 * @group roster
 */
class RetireRefereeFailureConditionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_basic_user_cannot_retire_a_bookable_referee()
    {
        $this->actAs(Role::BASIC);
        $referee = RefereeFactory::new()->bookable()->create();

        $response = $this->retireRequest($referee);

        $response->assertForbidden();
    }

    /** @test */
    public function a_basic_user_cannot_retire_an_injured_referee()
    {
        $this->actAs(Role::BASIC);
        $referee = RefereeFactory::new()->injured()->create();

        $response = $this->retireRequest($referee);

        $response->assertForbidden();
    }

    /** @test */
    public function a_basic_user_cannot_retire_a_suspended_referee()
    {
        $this->actAs(Role::BASIC);
        $referee = RefereeFactory::new()->suspended()->create();

        $response = $this->retireRequest($referee);

        $response->assertForbidden();
    }
}
