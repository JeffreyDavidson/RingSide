<?php

namespace App\Models\Concerns;

use App\Models\Employment;
use App\Traits\HasCachedAttributes;
use Illuminate\Database\Eloquent\Builder;

trait CanBeEmployed
{
    /**
     * Undocumented function.
     *
     * @return void
     */
    public static function bootCanBeEmployed()
    {
        if (config('app.debug')) {
            $traits = class_uses_recursive(static::class);

            if (! in_array(HasCachedAttributes::class, $traits)) {
                throw new \LogicException('CanBeEmployed trait used without HasCachedAttributes trait');
            }
        }
    }

    /**
     * Get all of the employments of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function employments()
    {
        return $this->morphMany(Employment::class, 'employable');
    }

    /**
     * Get the current employment of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function currentEmployment()
    {
        return $this->morphOne(Employment::class, 'employable')
                    ->where('started_at', '<=', now())
                    ->whereNull('ended_at');
    }

    /**
     * Get the pending employment of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function futureEmployment()
    {
        return $this->morphOne(Employment::class, 'employable')
            ->where('started_at', '>', now())
            ->whereNull('ended_at');
    }

    /**
     * Get the previous employments of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function previousEmployments()
    {
        return $this->employments()
                    ->whereNotNull('ended_at');
    }

    /**
     * Get the previous employment of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function previousEmployment()
    {
        return $this->employments()
                    ->whereNotNull('ended_at')
                    ->latest('ended_at')
                    ->limit(1);
    }

    /**
     * Determine if a model is employed.
     *
     * @return bool
     */
    public function getIsCurrentlyEmployedCachedAttribute()
    {
        return $this->isCurrentlyEmployed();
    }

    /**
     * Determine if a model is employed.
     *
     * @return bool
     */
    public function getIsUnemployedCachedAttribute()
    {
        return ! $this->isCurrentlyEmployed();
    }

    /**
     * Scope a query to only include pending employment models.
     * These model have not been employed.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     */
    public function getHasPendingEmploymentCachedAttribute()
    {
        if (! $this->currentEmployment) {
            return true;
        }

        return $this->whereHas('pendingEmployment');
    }

    /**
     * Scope a query to only include pending employment models.
     * These model have not been employed.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopePendingEmployment($query)
    {
        return $query->where('status', 'pending-employment');
    }

    /**
     * Scope a query to only include employed models.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeEmployed($query)
    {
        return $query->whereHas('currentEmployment');
    }

    /**
     * Scope a query to only include employed models.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeReleased($query)
    {
        return $query->whereDoesntHave('employments', function (Builder $query) {
            $query->whereNull('ended_at');
        })->whereDoesntHave('currentRetirement');
    }

    /**
     * Employ a model.
     *
     * @param  Carbon|string $startedAt
     * @return bool
     */
    public function employ($startedAt = null)
    {
        $startDate = $startedAt ?? now();

        $this->employments()->updateOrCreate(['ended_at' => null], ['started_at' => $startDate]);

        return $this->touch();
    }

    /**
     * Release a model.
     *
     * @param  Carbon|string $releasedAt
     * @return bool
     */
    public function release($releasedAt = null)
    {
        if ($this->isSuspended()) {
            $this->reinstate();
        }

        if ($this->isInjured()) {
            $this->clearFromInjury();
        }

        $releaseDate = $releasedAt ?? now();
        $this->currentEmployment()->update(['ended_at' => $releaseDate]);

        return $this->touch();
    }

    /**
     * Check to see if the model is employed.
     *
     * @return bool
     */
    public function isCurrentlyEmployed()
    {
        return $this->currentEmployment()->exists();
    }

    /**
     * Check to see if the model is employed.
     *
     * @return bool
     */
    public function isUnemployed()
    {
        return $this->employments()->doesntExist();
    }

    /**
     * Check to see if the model has a future scheduled employment.
     *
     * @return bool
     */
    public function hasFutureEmployment()
    {
        return $this->futureEmployment()->exists();
    }

    /**
     * Check to see if the model has been released.
     *
     * @return bool
     */
    public function isReleased()
    {
        return $this->employments()->whereNull('ended_at')->doesntExist();
    }

    /**
     * Determine if the model can be employed.
     *
     * @return bool
     */
    public function canBeEmployed()
    {
        if ($this->isCurrentlyEmployed()) {
            return false;
        }

        if ($this->isRetired()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the model can be released.
     *
     * @return bool
     */
    public function canBeReleased()
    {
        if ($this->isUnemployed()) {
            return false;
        }

        if ($this->hasFutureEmployment()) {
            return false;
        }

        if ($this->isReleased()) {
            return false;
        }

        if ($this->isRetired()) {
            return false;
        }

        return true;
    }

    /**
     * Get the model's first employment date.
     *
     * @return string|null
     */
    public function getStartedAtAttribute()
    {
        return optional($this->employments->first())->started_at;
    }

    /**
     * Get the current employment of the model.
     *
     * @return App\Models\Employment
     */
    public function getCurrentEmploymentAttribute()
    {
        if (! $this->relationLoaded('currentEmployment')) {
            $this->setRelation('currentEmployment', $this->currentEmployment()->get());
        }

        return $this->getRelation('currentEmployment')->first();
    }

    /**
     * Get the previous employment of the model.
     *
     * @return App\Models\Employment
     */
    public function getPreviousEmploymentAttribute()
    {
        if (! $this->relationLoaded('previousEmployment')) {
            $this->setRelation('previousEmployment', $this->previousEmployment()->get());
        }

        return $this->getRelation('previousEmployment')->first();
    }

    /**
     * Get the previous employment of the model.
     *
     * @return App\Models\Employment
     */
    public function getFutureEmploymentAttribute()
    {
        if (! $this->relationLoaded('futureEmployment')) {
            $this->setRelation('futureEmployment', $this->futureEmployment()->get());
        }

        return $this->getRelation('futureEmployment')->first();
    }
}
