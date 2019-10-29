<?php

namespace Redsnapper\LaravelDoorman\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Redsnapper\LaravelDoorman\Models\Interfaces\RoleInterface;
use Redsnapper\LaravelDoorman\PermissionsRegistrar;

trait HasRoles
{
    /**
     * @param  RoleInterface  $role
     * @return Model
     */
    public function assignRole(RoleInterface $role): self
    {
        $this->roles()->syncWithoutDetaching($role->getKey());

        return $this;
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(app(PermissionsRegistrar::class)->getRoleClass());
    }

    /**
     *  Determine if the model has (one of) the given role(s).
     *
     * @param  Collection|RoleInterface  $roles
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if ($roles instanceof RoleInterface) {
            return $this->roles->contains(app(PermissionsRegistrar::class)->getRoleClass()->getKeyName(),
              $roles->getKey());
        }

        return $roles->intersect($this->roles)->isNotEmpty();
    }
}