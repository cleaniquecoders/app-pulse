<?php

namespace CleaniqueCoders\AppPulse\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;
use CleaniqueCoders\Traitify\Contracts\Enum as Contract;

/**
 * Enum SiteStatus
 *
 * Defines the operational status of a site, indicating whether it is accessible or down.
 */
enum SiteStatus: string implements Contract
{
    use InteractsWithEnum;

    /**
     * Site is operational and accessible.
     */
    case UP = 'up';

    /**
     * Site is not operational and inaccessible.
     */
    case DOWN = 'down';

    /**
     * Get the label for the site status.
     */
    public function label(): string
    {
        return match ($this) {
            self::UP => __('Up'),
            self::DOWN => __('Down'),
        };
    }

    /**
     * Get a description for the site status.
     */
    public function description(): string
    {
        return match ($this) {
            self::UP => __('The site is currently up and running.'),
            self::DOWN => __('The site is currently down and not available.'),
        };
    }
}
