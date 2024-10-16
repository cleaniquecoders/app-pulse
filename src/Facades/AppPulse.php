<?php

namespace CleaniqueCoders\AppPulse\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CleaniqueCoders\AppPulse\AppPulse
 */
class AppPulse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CleaniqueCoders\AppPulse\AppPulse::class;
    }
}
