<?php

namespace Tests\Feature\Admin\Titles;

use TitleFactory;
use App\Enums\Role;
use Tests\TestCase;
use App\Models\Title;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group titles
 * @group admins
 */
class IntroduceTitleSuccessConditionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_administrator_can_introduce_a_pending_introduction_title()
    {
        $this->actAs(Role::ADMINISTRATOR);
        $title = TitleFactory::new()->pendingIntroduction()->create();

        $response = $this->introduceRequest($title);

        $response->assertRedirect(route('titles.index'));
        tap($title->fresh(), function ($title) {
            $this->assertTrue($title->isCompetable());
        });
    }
}
