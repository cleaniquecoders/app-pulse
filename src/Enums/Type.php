<?php

namespace CleaniqueCoders\AppPulse\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;
use CleaniqueCoders\Traitify\Contracts\Enum as Contract;

/**
 * Enum Type
 *
 * Represents types of checks available within the application, such as uptime and SSL validation.
 */
enum Type: string implements Contract
{
    use InteractsWithEnum;

    /**
     * Uptime monitoring type.
     */
    case UPTIME = 'uptime';

    /**
     * SSL monitoring type.
     */
    case SSL = 'ssl';

    /**
     * Get the label for the enum value.
     */
    public function label(): string
    {
        return match ($this) {
            self::UPTIME => __('Uptime'),
            self::SSL => __('SSL'),
        };
    }

    /**
     * Get a description for the enum value.
     */
    public function description(): string
    {
        return match ($this) {
            self::UPTIME => __('Check the uptime of the site.'),
            self::SSL => __('Check SSL validity of the site.'),
        };
    }
}
