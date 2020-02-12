<?php

use App\Enums\WrestlerStatus;
use App\Models\TagTeam;
use App\Models\Wrestler;
use Illuminate\Support\Str;

class WrestlerFactory extends BaseFactory
{
    /** @var EmploymentFactory|null */
    public $employmentFactory;
    /** @var SuspensionFactory|null */
    public $suspensionFactory;
    /** @var InjuryFactory|null */
    public $injuryFactory;
    /** @var RetirementFactory|null */
    public $retirementFactory;
    /** @var TagTeam */
    public $tagTeam;
    public $softDeleted = false;
    protected $factoriesToClone = [
        'employmentFactory',
        'suspensionFactory',
        'injuryFactory',
        'retirementFactory',
    ];

    public function forTagTeam(TagTeam $tagTeam)
    {
        $clone = clone $this;
        $clone->tagTeam = $tagTeam;

        return $clone;
    }

    public function employed(EmploymentFactory $employmentFactory = null)
    {
        $clone = clone $this;
        $clone->employmentFactory = $employmentFactory ?? EmploymentFactory::new();

        return $clone;
    }

    public function unemployed()
    {
        $clone = clone $this;
        $clone->employmentFactory = null;

        return $clone;
    }

    public function pendingEmployment(EmploymentFactory $employmentFactory = null)
    {
        $clone = clone $this;
        $clone->status = WrestlerStatus::PENDING_EMPLOYMENT;
        // We set these to null since we can't be pending employment if they're set
        $clone->employmentFactory = $employmentFactory ?? EmploymentFactory::new()->started(now()->addDays(2));
        $clone->suspensionFactory = null;
        $clone->injuryFactory = null;
        $clone->retirementFactory = null;

        return $clone;
    }

    public function bookable(EmploymentFactory $employmentFactory = null)
    {
        $clone = clone $this;
        $clone->status = WrestlerStatus::BOOKABLE;
        $clone = $clone->employed($employmentFactory ?? $this->employmentFactory);
        // We set these to null since a TagTeam cannot be bookable if any of these exist
        $clone->suspensionFactory = null;
        $clone->injuryFactory = null;
        $clone->retirementFactory = null;

        return $clone;
    }

    public function injured(InjuryFactory $injuryFactory = null, EmploymentFactory $employmentFactory = null)
    {
        $clone = clone $this;
        $clone->status = WrestlerStatus::INJURED;
        $clone->injuryFactory = $injuryFactory ?? InjuryFactory::new();
        // We set the employment factory since a wrestler must be employed to be injured
        $clone = $clone->employed($employmentFactory ?? $this->employmentFactory);

        return $clone;
    }

    public function suspended(SuspensionFactory $suspensionFactory = null, EmploymentFactory $employmentFactory = null)
    {
        $clone = clone $this;
        $clone->status = WrestlerStatus::SUSPENDED;
        $clone->suspensionFactory = $suspensionFactory ?? SuspensionFactory::new();
        // We set the employment factory since a wrestler must be employed to be suspended
        $clone = $clone->employed($employmentFactory ?? $this->employmentFactory);

        return $clone;
    }

    public function retired(RetirementFactory $retirementFactory = null, EmploymentFactory $employmentFactory = null)
    {
        $clone = clone $this;
        $clone->status = WrestlerStatus::RETIRED;
        $clone->retirementFactory = $retirementFactory ?? RetirementFactory::new();
        // We set the employment factory since a wrestler must be employed to retire
        $clone = $clone->employed($employmentFactory ?? $this->employmentFactory);

        return $clone;
    }

    public function create($attributes = [])
    {
        return $this->make(function ($attributes) {
            $wrestler = Wrestler::create($this->resolveAttributes($attributes));

            if ($this->employmentFactory) {
                $this->employmentFactory->forWrestler($wrestler)->create();
            }

            if ($this->suspensionFactory) {
                $this->suspensionFactory->forWrestler($wrestler)->create();
            }

            if ($this->retirementFactory) {
                $this->retirementFactory->forWrestler($wrestler)->create();
            }

            if ($this->injuryFactory) {
                $this->injuryFactory->forWrestler($wrestler)->create();
            }

            if ($this->tagTeam) {
                $this->tagTeam->currentWrestlers()->attach($wrestler);
            }

            $wrestler->save();

            if ($this->softDeleted) {
                $wrestler->delete();
            }

            return $wrestler;
        }, $attributes);
    }

    protected function defaultAttributes(Faker\Generator $faker)
    {
        return [
            'name' => $faker->name,
            'height' => $faker->numberBetween(60, 95),
            'weight' => $faker->numberBetween(180, 500),
            'hometown' => $faker->city.', '.$faker->state,
            'signature_move' => Str::title($faker->words(3, true)),
            'status' => WrestlerStatus::__default,
        ];
    }
}
