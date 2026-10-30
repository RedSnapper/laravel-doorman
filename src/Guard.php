<?php

namespace Redsnapper\LaravelDoorman;

class Guard
{
    /**
     * Get the provider model for this guard
     * @param  string|null  $name
     * @return string|null
     */
    public static function getModelFor(?string $name = null):?string
    {
        $guard = $name ?? static::getDefaultName();

        if(isset(config('auth.guards')[$guard]['provider'])){
            $provider = config('auth.guards')[$guard]['provider'];
            return config("auth.providers.{$provider}.model");
        }

        return null;

    }

    public static function getDefaultName(): string
    {
        return config('auth.defaults.guard');
    }
}