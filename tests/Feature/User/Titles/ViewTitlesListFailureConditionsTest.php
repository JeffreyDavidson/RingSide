<?php

namespace Tests\Feature\User\Titles;

use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group titles
 * @group users
 */
class ViewTitlesListFailureConditionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_basic_user_cannot_view_titles_page()
    {
        $this->actAs(Role::BASIC);

        $response = $this->indexRequest('titles');

        $response->assertForbidden();
    }
}
