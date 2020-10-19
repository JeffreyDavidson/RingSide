<?php

namespace App\Models;

use App\Enums\ManagerStatus;
use App\Exceptions\CannotBeClearedFromInjuryException;
use App\Exceptions\CannotBeEmployedException;
use App\Exceptions\CannotBeInjuredException;
use App\Exceptions\CannotBeReinstatedException;
use App\Exceptions\CannotBeReleasedException;
use App\Exceptions\CannotBeRetiredException;
use App\Exceptions\CannotBeSuspendedException;
use App\Exceptions\CannotBeUnretiredException;
use App\Models\Employment;
use App\Models\Injury;
use App\Models\Retirement;
use App\Models\Suspension;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manager extends Model
{
    use SoftDeletes,
        HasFactory,
        Concerns\HasFullName,
        Concerns\CanBeStableMember,
        Concerns\Unguarded;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($manager) {
            $manager->updateStatus();
        });
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => ManagerStatus::class,
    ];

    /**
     * Get the user belonging to the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include available managers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', ManagerStatus::AVAILABLE);
    }

    /**
     * Check to see if the manager is available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->currentEmployment()->exists();
    }

    /**
     * Get all of the employments of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function employments()
    {
        return $this->morphMany(Employment::class, 'employable');
    }

    /**
     * Get the current employment of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function currentEmployment()
    {
        return $this->morphOne(Employment::class, 'employable')
                    ->where('started_at', '<=', now())
                    ->where('ended_at', '=', null)
                    ->limit(1);
    }

    /**
     * Get the future employment of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function futureEmployment()
    {
        return $this->morphOne(Employment::class, 'employable')
                    ->where('started_at', '>', now())
                    ->whereNull('ended_at')
                    ->limit(1);
    }

    /**
     * Get the previous employments of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function previousEmployments()
    {
        return $this->employments()
                    ->whereNotNull('ended_at');
    }

    /**
     * Get the previous employment of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function previousEmployment()
    {
        return $this->morphOne(Employment::class, 'employable')
                    ->latest('ended_at')
                    ->limit(1);
    }

    /**
     * Scope a query to only include future employed managers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFutureEmployed($query)
    {
        return $query->whereHas('futureEmployment');
    }

    /**
     * Scope a query to only include employed managers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEmployed($query)
    {
        return $query->whereHas('currentEmployment');
    }

    /**
     * Scope a query to only include released managers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReleased($query)
    {
        return $query->whereHas('previousEmployment')
                    ->whereDoesntHave('currentEmployment')
                    ->whereDoesntHave('currentRetirement');
    }

    /**
     * Scope a query to only include unemployed managers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnemployed($query)
    {
        return $query->whereDoesntHave('currentEmployment')
                    ->orWhereDoesntHave('previousEmployments');
    }

    /**
     * Scope a query to include first employment date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithFirstEmployedAtDate($query)
    {
        return $query->addSelect(['first_employed_at' => Employment::select('started_at')
            ->whereColumn('employable_id', $query->qualifyColumn('id'))
            ->where('employable_type', $this->getMorphClass())
            ->orderBy('started_at', 'desc')
            ->limit(1),
        ])->withCasts(['first_employed_at' => 'datetime']);
    }

    /**
     * Scope a query to order by the managers first employment date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByFirstEmployedAtDate($query, $direction = 'asc')
    {
        return $query->orderByRaw("DATE(first_employed_at) $direction");
    }

    /**
     * Scope a query to include released date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithReleasedAtDate($query)
    {
        return $query->addSelect(['released_at' => Employment::select('ended_at')
            ->whereColumn('employable_id', $this->getTable().'.id')
            ->where('employable_type', $this->getMorphClass())
            ->orderBy('ended_at', 'desc')
            ->limit(1),
        ])->withCasts(['released_at' => 'datetime']);
    }

    /**
     * Scope a query to order by the managers current released date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByCurrentReleasedAtDate($query, $direction = 'asc')
    {
        return $query->orderByRaw("DATE(current_released_at) $direction");
    }

    /**
     * Employ a manager.
     *
     * @param  string|null $startedAt
     * @return void
     */
    public function employ($startedAt = null)
    {
        throw_unless($this->canBeEmployed(), new CannotBeEmployedException('Entity cannot be employed. This entity is currently employed.'));

        $startDate = $startedAt ?? now();

        $this->employments()->updateOrCreate(['ended_at' => null], ['started_at' => $startDate]);
        $this->updateStatusAndSave();
    }

    /**
     * Release a manager.
     *
     * @param  string|null $releasedAt
     * @return void
     */
    public function release($releasedAt = null)
    {
        throw_unless($this->canBeReleased(), new CannotBeReleasedException('Entity cannot be released. This entity does not have an active employment.'));

        if ($this->isSuspended()) {
            $this->reinstate();
        }

        if ($this->isInjured()) {
            $this->clearFromInjury();
        }

        $releaseDate = $releasedAt ?? now();

        $this->currentEmployment()->update(['ended_at' => $releaseDate]);
        $this->updateStatusAndSave();
    }

    /**
     * Check to see if the manager is employed.
     *
     * @return bool
     */
    public function isCurrentlyEmployed()
    {
        return $this->currentEmployment()->exists();
    }

    /**
     * Check to see if the manager is not in employment.
     *
     * @return bool
     */
    public function isNotInEmployment()
    {
        return $this->isUnemployed() || $this->isReleased() || $this->hasFutureEmployment() || $this->isRetired();
    }

    /**
     * Check to see if the manager is unemployed.
     *
     * @return bool
     */
    public function isUnemployed()
    {
        return $this->employments()->count() === 0;
    }

    /**
     * Check to see if the manager has a future employment.
     *
     * @return bool
     */
    public function hasFutureEmployment()
    {
        return $this->futureEmployment()->exists();
    }

    /**
     * Check to see if the manager has been released.
     *
     * @return bool
     */
    public function isReleased()
    {
        return $this->previousEmployment()->exists() &&
                $this->futureEmployment()->doesntExist() &&
                $this->currentEmployment()->doesntExist() &&
                $this->currentRetirement()->doesntExist();
    }

    /**
     * Determine if the manager can be employed.
     *
     * @return bool
     */
    public function canBeEmployed()
    {
        if ($this->isCurrentlyEmployed()) {
            // throw new CannotBeEmployedException('Entity cannot be employed. This entity is currently employed.');
            return false;
        }

        if ($this->isRetired()) {
            // throw new CannotBeEmployedException('Entity cannot be employed. This entity does not have an active employment.');
            return false;
        }

        return true;
    }

    /**
     * Determine if the manager can be released.
     *
     * @return bool
     */
    public function canBeReleased()
    {
        if ($this->isNotInEmployment()) {
            // throw new CannotBeReleasedException('Entity cannot be released. This entity does not have an active employment.');
            return false;
        }

        return true;
    }

    /**
     * Get the manager's first employment date.
     *
     * @return string|null
     */
    public function getStartedAtAttribute()
    {
        return optional($this->employments->last())->started_at;
    }

    /**
     * Get the retirements of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function retirements()
    {
        return $this->morphMany(Retirement::class, 'retiree');
    }

    /**
     * Get the current retirement of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function currentRetirement()
    {
        return $this->morphOne(Retirement::class, 'retiree')
                    ->where('started_at', '<=', now())
                    ->whereNull('ended_at')
                    ->limit(1);
    }

    /**
     * Get the previous retirements of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function previousRetirements()
    {
        return $this->retirements()
                    ->whereNotNull('ended_at');
    }

    /**
     * Get the previous retirement of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function previousRetirement()
    {
        return $this->morphOne(Retirement::class, 'retiree')
                    ->latest('ended_at')
                    ->limit(1);
    }

    /**
     * Scope a query to only include retired managers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRetired($query)
    {
        return $query->whereHas('currentRetirement');
    }

    /**
     * Scope a query to include current retirement date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCurrentRetiredAtDate($query)
    {
        return $query->addSelect(['current_retired_at' => Retirement::select('started_at')
            ->whereColumn('retiree_id', $this->getTable().'.id')
            ->where('retiree_type', $this->getMorphClass())
            ->oldest('started_at')
            ->limit(1),
        ])->withCasts(['current_retired_at' => 'datetime']);
    }

    /**
     * Scope a query to order by the manager's current retirement date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByCurrentRetiredAtDate($query, $direction = 'asc')
    {
        return $query->orderByRaw("DATE(current_retired_at) $direction");
    }

    /**
     * Retire a manager.
     *
     * @param  string|null $retiredAt
     * @return void
     */
    public function retire($retiredAt = null)
    {
        throw_unless($this->canBeRetired(), new CannotBeRetiredException('Entity cannot be retired. This entity does not have an active employment.'));

        if ($this->isSuspended()) {
            $this->reinstate();
        }

        if ($this->isInjured()) {
            $this->clearFromInjury();
        }

        $retiredDate = $retiredAt ?: now();

        $this->currentEmployment()->update(['ended_at' => $retiredDate]);
        $this->retirements()->create(['started_at' => $retiredDate]);
        $this->updateStatusAndSave();
    }

    /**
     * Unretire a manager.
     *
     * @param  string|null $unretiredAt
     * @return void
     */
    public function unretire($unretiredAt = null)
    {
        throw_unless($this->canBeUnretired(), new CannotBeUnretiredException('Entity cannot be unretired. This entity is not retired.'));

        $unretiredDate = $unretiredAt ?: now();

        $this->currentRetirement()->update(['ended_at' => $unretiredDate]);
        $this->employments()->create(['started_at' => $unretiredDate]);
        $this->updateStatusAndSave();
    }

    /**
     * Check to see if the manager is retired.
     *
     * @return bool
     */
    public function isRetired()
    {
        return $this->currentRetirement()->exists();
    }

    /**
     * Determine if the manager can be retired.
     *
     * @return bool
     */
    public function canBeRetired()
    {
        if ($this->isNotInEmployment()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the manager can be unretired.
     *
     * @return bool
     */
    public function canBeUnretired()
    {
        if (! $this->isRetired()) {
            return false;
        }

        return true;
    }

    /**
     * Get the suspensions of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function suspensions()
    {
        return $this->morphMany(Suspension::class, 'suspendable');
    }

    /**
     * Get the current suspension of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function currentSuspension()
    {
        return $this->morphOne(Suspension::class, 'suspendable')
                    ->whereNull('ended_at')
                    ->limit(1);
    }

    /**
     * Get the previous suspensions of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function previousSuspensions()
    {
        return $this->suspensions()
                    ->whereNotNull('ended_at');
    }

    /**
     * Get the previous suspension of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function previousSuspension()
    {
        return $this->morphOne(Suspension::class, 'suspendable')
                    ->latest('ended_at')
                    ->limit(1);
    }

    /**
     * Scope a query to only include suspended managers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuspended($query)
    {
        return $query->whereHas('currentSuspension');
    }

    /**
     * Scope a query to include current suspension date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCurrentSuspendedAtDate($query)
    {
        return $query->addSelect(['current_suspended_at' => Suspension::select('started_at')
            ->whereColumn('suspendable_id', $query->qualifyColumn('id'))
            ->where('suspendable_type', $this->getMorphClass())
            ->orderBy('started_at', 'desc')
            ->limit(1),
        ])->withCasts(['current_suspended_at' => 'datetime']);
    }

    /**
     * Scope a query to order by the manager's current suspension date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByCurrentSuspendedAtDate($query, $direction = 'asc')
    {
        return $query->orderByRaw("DATE(current_suspended_at) $direction");
    }

    /**
     * Suspend a manager.
     *
     * @param  string|null $suspendedAt
     * @return void
     */
    public function suspend($suspendedAt = null)
    {
        throw_unless($this->canBeSuspended(), new CannotBeSuspendedException('Entity cannot be suspended. This entity does not have an active employment.'));

        $suspensionDate = $suspendedAt ?? now();

        $this->suspensions()->create(['started_at' => $suspensionDate]);
        $this->updateStatusAndSave();
    }

    /**
     * Reinstate a manager.
     *
     * @param  string|null $reinstatedAt
     * @return void
     */
    public function reinstate($reinstatedAt = null)
    {
        throw_unless($this->canBeReinstated(), new CannotBeReinstatedException('Entity cannot be unretired. This entity is not retired.'));

        $reinstatedDate = $reinstatedAt ?: now();

        $this->currentSuspension()->update(['ended_at' => $reinstatedDate]);
        $this->updateStatusAndSave();
    }

    /**
     * Check to see if the manager is suspended.
     *
     * @return bool
     */
    public function isSuspended()
    {
        return $this->currentSuspension()->exists();
    }

    /**
     * Determine if the manager can be suspended.
     *
     * @return bool
     */
    public function canBeSuspended()
    {
        if ($this->isNotInEmployment()) {
            // throw new CannotBeSuspendedException('Entity cannot be suspended. This entity does not have an active employment.');
            return false;
        }

        if ($this->isSuspended()) {
            // throw new CannotBeSuspendedException('Entity cannot be suspended. This entity is currently suspended.');
            return false;
        }

        if ($this->isInjured()) {
            // throw new CannotBeSuspendedException('Entity cannot be suspended. This entity is currently injured.');
            return false;
        }

        return true;
    }

    /**
     * Determine if the manager can be reinstated.
     *
     * @return bool
     */
    public function canBeReinstated()
    {
        if (! $this->isSuspended()) {
            // throw new CannotBeReinstatedException('Entity cannot be reinstated. This entity is not suspended.');
            return false;
        }

        return true;
    }

    /**
     * Get the injuries of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function injuries()
    {
        return $this->morphMany(Injury::class, 'injurable');
    }

    /**
     * Get the current injury of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function currentInjury()
    {
        return $this->morphOne(Injury::class, 'injurable')
                    ->whereNull('ended_at')
                    ->limit(1);
    }

    /**
     * Get the previous injuries of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function previousInjuries()
    {
        return $this->injuries()
                    ->whereNotNull('ended_at');
    }

    /**
     * Get the previous injury of the manager.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function previousInjury()
    {
        return $this->morphOne(Injury::class, 'injurable')
                    ->latest('ended_at')
                    ->limit(1);
    }

    /**
     * Scope a query to only include injured managers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInjured($query)
    {
        return $query->whereHas('currentInjury');
    }

    /**
     * Scope a query to include current injured date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCurrentInjuredAtDate($query)
    {
        return $query->addSelect(['current_injured_at' => Injury::select('started_at')
            ->whereColumn('injurable_id', $query->qualifyColumn('id'))
            ->where('injurable_type', $this->getMorphClass())
            ->orderBy('started_at', 'desc')
            ->limit(1),
        ])->withCasts(['current_injured_at' => 'datetime']);
    }

    /**
     * Scope a query to order by the managers current injured date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string|null $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByCurrentInjuredAtDate($query, $direction = 'asc')
    {
        return $query->orderByRaw("DATE(current_injured_at) $direction");
    }

    /**
     * Injure a manager.
     *
     * @param  string|null $injuredAt
     * @return void
     */
    public function injure($injuredAt = null)
    {
        throw_unless($this->canBeInjured(), new CannotBeInjuredException('Entity cannot be injured. This entity does not have an active employment.'));

        $injuredDate = $injuredAt ?? now();

        $this->injuries()->create(['started_at' => $injuredDate]);
        $this->updateStatusAndSave();
    }

    /**
     * Mark a manager cleared from an injury.
     *
     * @param  string|null $recoveredAt
     * @return void
     */
    public function clearFromInjury($recoveredAt = null)
    {
        throw_unless($this->canBeClearedFromInjury(), new CannotBeClearedFromInjuryException('Entity cannot be cleared from an injury. This entity is not injured.'));

        $recoveryDate = $recoveredAt ?? now();

        $this->currentInjury()->update(['ended_at' => $recoveryDate]);
        $this->updateStatusAndSave();
    }

    /**
     * Check to see if the manager is injured.
     *
     * @return bool
     */
    public function isInjured()
    {
        return $this->currentInjury()->exists();
    }

    /**
     * Determine if the manager can be injured.
     *
     * @return bool
     */
    public function canBeInjured()
    {
        if ($this->isNotInEmployment()) {
            // throw new CannotBeInjuredException('Entity cannot be injured. This entity does not have an active employment.');
            return false;
        }

        if ($this->isInjured()) {
            // throw new CannotBeInjuredException('Entity cannot be injured. This entity is currently injured.');
            return false;
        }

        if ($this->isSuspended()) {
            // throw new CannotBeInjuredException('Entity cannot be injured. Thokis entity is currently suspended.');
            return false;
        }

        return true;
    }

    /**
     * Determine if the manager can be cleared from an injury.
     *
     * @return bool
     */
    public function canBeClearedFromInjury()
    {
        if (! $this->isInjured()) {
            // throw new CannotBeClearedFromInjuryException('Entity cannot be marked as being recovered from an injury. This entity is not injured.');
            return false;
        }

        return true;
    }

    /**
     * Update the status for the manager.
     *
     * @return void
     */
    public function updateStatus()
    {
        if ($this->isCurrentlyEmployed()) {
            if ($this->isInjured()) {
                $this->status = ManagerStatus::INJURED;
            } elseif ($this->isSuspended()) {
                $this->status = ManagerStatus::SUSPENDED;
            } elseif ($this->isAvailable()) {
                $this->status = ManagerStatus::AVAILABLE;
            }
        } elseif ($this->hasFutureEmployment()) {
            $this->status = ManagerStatus::FUTURE_EMPLOYMENT;
        } elseif ($this->isReleased()) {
            $this->status = ManagerStatus::RELEASED;
        } elseif ($this->isRetired()) {
            $this->status = ManagerStatus::RETIRED;
        } else {
            $this->status = ManagerStatus::UNEMPLOYED;
        }
    }

    /**
     * Updates a manager's status and saves.
     *
     * @return void
     */
    public function updateStatusAndSave()
    {
        $this->updateStatus();
        $this->save();
    }
}
