<?php

namespace Tests\Feature\User\Managers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Manager;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group managers
 * @group users
 * @group roster
 */
class ViewManagerBioPageFailureConditionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_basic_user_cannot_view_another_users_manager_profile()
    {
        $this->actAs('basic-user');
        $otherUser = factory(User::class)->create();
        $manager = factory(Manager::class)->create(['user_id' => $otherUser->id]);

        $response = $this->showRequest($manager);

        $response->assertForbidden();
    }
}
