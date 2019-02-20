<?php

namespace App;

use App\Traits\Hireable;
use App\Traits\Injurable;
use App\Traits\Retireable;
use App\Traits\Activatable;
use App\Traits\Suspendable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wrestler extends Model
{
    use SoftDeletes, Retireable, Suspendable, Injurable, Activatable, Hireable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['hired_at', 'deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
     protected static function boot()
     {
        parent::boot();

        static::creating(function ($model) {
            $model->slug = Str::slug($model->name);
        });
     }
    /**
     * Get the tag teams the wrestler has belonged to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tagteams()
    {
        return $this->belongsToMany(TagTeam::class);
    }

    /**
     * Get the current tag team of the wrestler.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tagteam()
    {
        return $this->belongsToMany(TagTeam::class)->where('is_active', true);
    }

    /**
     * Scope a query to only include wrestlers of a given state.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasState($query, $state)
    {
        $scope = 'scope' . Str::studly($state);

        if (method_exists($this, $scope)) {
            return $this->{$scope}($query);
        }
    }

    /**
     * Return the wrestler's height formatted.
     *
     * @return string
     */
    public function getFormattedHeightAttribute()
    {
        $feet = floor($this->height / 12);
        $inches = ($this->height % 12);

        return $feet . '\'' . $inches . '"';
    }
}
