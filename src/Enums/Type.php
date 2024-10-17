<?php

namespace CleaniqueCoders\AppPulse\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;
use CleaniqueCoders\Traitify\Contracts\Enum as Contract;

enum Type: string implements Contract
{
    use InteractsWithEnum;

    case UPTIME = 'uptime';
    case SSL = 'ssl';

    public function label(): string
    {
        return match ($this) {
            self::UPTIME => __('Uptime'),
            self::SSL => __('SSL'),
            default => throw new \Exception('Unknown enum value requested for the label'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::UPTIME => __('Check the uptime of the site.'),
            self::SSL => __('Check SSL validity of the site.'),
            default => throw new \Exception('Unknown enum value requested for the description'),
        };
    }
}
