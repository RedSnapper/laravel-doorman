<?php

namespace Redsnapper\LaravelDoorman\Models\Traits;

use Exception;
use Redsnapper\LaravelDoorman\Models\Interfaces\PermissionInterface;
use Redsnapper\LaravelDoorman\Models\Interfaces\RoleInterface;
use Redsnapper\LaravelDoorman\PermissionsRegistrar;

trait GoesThroughDoorman
{
    use HasRoles;

    /**
     * @param  string  $permission
     * @return bool
     * @throws Exception
     */
    public function hasPermissionTo(string $permission): bool
    {
        $permission = app(PermissionsRegistrar::class)->getPermissionClass()->findByName($permission);

        return ($permission->isActive() && $this->hasPermission($permission));
    }

    public function hasPermission(PermissionInterface $permission): bool
    {
        return $permission->roles
          ->pluck(app(PermissionsRegistrar::class)->getPermissionClass()->getKeyName())
          ->intersect(
            $this->roles->pluck(app(PermissionsRegistrar::class)->getRoleClass()->getKeyName())
          )->isNotEmpty();
    }

    /**
     * @param  RoleInterface  $role
     * @return $this
     */
    public function assignRole(RoleInterface $role): self
    {
        $this->roles()->syncWithoutDetaching($role->getKey());

        return $this;
    }
}