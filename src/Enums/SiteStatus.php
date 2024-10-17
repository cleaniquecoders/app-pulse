<?php

namespace CleaniqueCoders\AppPulse\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;
use CleaniqueCoders\Traitify\Contracts\Enum as Contract;

enum SiteStatus: string implements Contract
{
    use InteractsWithEnum;

    case UP = 'up';
    case DOWN = 'down';

    public function label(): string
    {
        return match ($this) {
            self::UP => __('Up'),
            self::DOWN => __('Down'),
            default => throw new \Exception('Unknown enum value requested for the label'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::UP => __('The site is currently up and running.'),
            self::DOWN => __('The site is currently down and not available.'),
            default => throw new \Exception('Unknown enum value requested for the description'),
        };
    }
}
