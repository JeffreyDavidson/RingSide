<?php

namespace Tests\Feature\Venues;

use Tests\TestCase;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;

/** @group venues */
class CreateVenueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Valid Parameters for request.
     *
     * @param  array $overrides
     * @return array
     */
    private function validParams($overrides = [])
    {
        return array_replace([
            'name' => 'Example Venue',
            'address1' => '123 Main Street',
            'address2' => 'Suite 100',
            'city' => 'Laraville',
            'state' => 'New York',
            'zip' => '12345',
        ], $overrides);
    }

    /** @test */
    public function an_administrator_can_view_the_form_for_creating_a_venue()
    {
        $this->actAs('administrator');

        $response = $this->get(route('venues.create'));

        $response->assertViewIs('venues.create');
    }

    /** @test */
    public function a_basic_user_cannot_view_the_form_for_creating_a_venue()
    {
        $this->actAs('basic-user');

        $response = $this->get(route('venues.create'));

        $response->assertStatus(403);
    }

    /** @test */
    public function a_guest_cannot_view_the_form_for_creating_a_venue()
    {
        $response = $this->get(route('venues.create'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function an_administrator_can_create_a_venue()
    {
        $this->actAs('administrator');

        $response = $this->post(route('venues.store'), $this->validParams());

        $response->assertRedirect(route('venues.index'));
        tap(Venue::first(), function ($venue) {
            $this->assertEquals('Example Venue', $venue->name);
            $this->assertEquals('123 Main Street', $venue->address1);
            $this->assertEquals('Suite 100', $venue->address2);
            $this->assertEquals('Laraville', $venue->city);
            $this->assertEquals('New York', $venue->state);
            $this->assertEquals(12345, $venue->zip);
        });
    }

    /** @test */
    public function a_venue_address2_is_optional()
    {
        $this->actAs('administrator');

        $response = $this->post(route('venues.store'), $this->validParams(['address2' => null]));

        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function a_basic_user_cannot_create_a_venue()
    {
        $this->actAs('basic-user');

        $response = $this->post(route('venues.store'), $this->validParams());

        $response->assertStatus(403);
    }

    /** @test */
    public function a_guest_cannot_create_a_venue()
    {
        $response = $this->post(route('venues.store'), $this->validParams());

        $response->assertRedirect(route('login'));
    }
}
