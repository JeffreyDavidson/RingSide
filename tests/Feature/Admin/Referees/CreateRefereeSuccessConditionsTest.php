<?php

namespace Tests\Feature\Admin\Referees;

use App\Enums\Role;
use App\Models\Referee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group referees
 * @group admins
 * @group roster
 */
class CreateRefereeSuccessConditionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Valid parameters for request.
     *
     * @param  array $overrides
     * @return array
     */
    private function validParams($overrides = [])
    {
        return array_replace([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'started_at' => now()->toDateTimeString(),
        ], $overrides);
    }

    /** @test */
    public function an_administrator_can_view_the_form_for_creating_a_referee()
    {
        $this->actAs(Role::ADMINISTRATOR);

        $response = $this->createRequest('referee');

        $response->assertViewIs('referees.create');
        $response->assertViewHas('referee', new Referee);
    }

    /** @test */
    public function an_administrator_can_create_a_referee()
    {
        $this->actAs(Role::ADMINISTRATOR);

        $response = $this->storeRequest('referee', $this->validParams());

        $response->assertRedirect(route('referees.index'));
    }
}
