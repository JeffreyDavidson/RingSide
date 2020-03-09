<?php

namespace Tests\Unit\Filters;

use App\Filters\Concerns\FiltersByStatus;
use App\Filters\TitleFilters;
use Tests\TestCase;

class TitleFiltersTest extends TestCase
{
    /** @var App\Filters\TitleFilters */
    protected $subject;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = app(TitleFilters::class);
    }

    /** @test */
    public function title_filters_include_filtering_by_status()
    {
        $this->assertUsesTrait(FiltersByStatus::class, $this->subject);
        $this->assertTrue(in_array('status', $this->subject->filters));
    }

    /** @test */
    public function title_filters_include_filtering_by_introduced_at_date()
    {
        $this->assertTrue(in_array('introduced_at', $this->subject->filters));
    }

    /** @test */
    public function titles_can_be_filtered_by()
    {
        $this->assertTrue(in_array('introduced_at', $this->subject->filters));
    }
}
