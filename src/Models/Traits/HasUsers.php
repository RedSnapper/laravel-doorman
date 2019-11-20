<?php

namespace Redsnapper\LaravelDoorman\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Redsnapper\LaravelDoorman\Guard;
use Redsnapper\LaravelDoorman\PermissionsRegistrar;

trait HasUsers
{
    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Guard::getModelFor());
    }
}
