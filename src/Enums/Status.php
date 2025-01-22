<?php

namespace CleaniqueCoders\AppPulse\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;
use CleaniqueCoders\Traitify\Contracts\Enum as Contract;

/**
 * Enum Status
 *
 * Defines the monitoring status of the site, enabled or disabled.
 */
enum Status: int implements Contract
{
    use InteractsWithEnum;

    /**
     * Monitoring is enabled.
     */
    case ENABLED = 1;

    /**
     * Monitoring is disabled.
     */
    case DISABLED = 0;

    /**
     * Get the label for the monitoring status.
     */
    public function label(): string
    {
        return match ($this) {
            self::ENABLED => __('Enabled'),
            self::DISABLED => __('Disabled'),
        };
    }

    /**
     * Get a description for the monitoring status.
     */
    public function description(): string
    {
        return match ($this) {
            self::ENABLED => __('The site is currently monitored.'),
            self::DISABLED => __('The site is currently not monitored.'),
        };
    }
}
