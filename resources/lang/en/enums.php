<?php

use App\Enums\EventStatus;
use App\Enums\TitleStatus;
use App\Enums\StableStatus;
use App\Enums\ManagerStatus;
use App\Enums\RefereeStatus;
use App\Enums\TagTeamStatus;
use App\Enums\WrestlerStatus;

return [
    TitleStatus::class => [
        TitleStatus::BOOKABLE => 'Bookable',
        TitleStatus::PENDING_INTRODUCTION => 'Pending Introduction',
        TitleStatus::RETIRED => 'Retired',
    ],
    WrestlerStatus::class => [
        WrestlerStatus::BOOKABLE => 'Bookable',
        WrestlerStatus::PENDING_EMPLOYMENT => 'Pending Employment',
        WrestlerStatus::RETIRED => 'Retired',
        WrestlerStatus::SUSPENDED => 'Suspended',
        WrestlerStatus::INJURED => 'Injured',
    ],
    TagTeamStatus::class => [
        TagTeamStatus::BOOKABLE => 'Bookable',
        TagTeamStatus::PENDING_EMPLOYMENT => 'Pending Employment',
        TagTeamStatus::RETIRED => 'Retired',
        TagTeamStatus::SUSPENDED => 'Suspended',
    ],
    RefereeStatus::class => [
        RefereeStatus::BOOKABLE => 'Bookable',
        RefereeStatus::PENDING_EMPLOYMENT => 'Pending Employment',
        RefereeStatus::RETIRED => 'Retired',
        RefereeStatus::SUSPENDED => 'Suspended',
        RefereeStatus::INJURED => 'Injured',
    ],
    ManagerStatus::class => [
        ManagerStatus::BOOKABLE => 'Bookable',
        ManagerStatus::PENDING_EMPLOYMENT => 'Pending Employment',
        ManagerStatus::RETIRED => 'Retired',
        ManagerStatus::SUSPENDED => 'Suspended',
        ManagerStatus::INJURED => 'Injured',
    ],
    StableStatus::class => [
        StableStatus::PENDING_EMPLOYMENT => 'Pending Employment',
    ],
    EventStatus::class => [
        EventStatus::SCHEDULED => 'Scheduled',
        EventStatus::PAST => 'Past',
    ],
];
