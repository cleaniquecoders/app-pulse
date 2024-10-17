<?php

namespace CleaniqueCoders\AppPulse\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;
use CleaniqueCoders\Traitify\Contracts\Enum as Contract;

enum SslStatus: string implements Contract
{
    use InteractsWithEnum;

    case EXPIRED = 'ssl_expired';
    case VALID = 'ssl_valid';
    case NOT_YET_VALID = 'ssl_not_yet_valid';
    case UNCHECKED = 'ssl_unchecked';
    case FAILED_PARSE = 'ssl_failed_parse';
    case FAILED_CHECK = 'ssl_failed_check';

    public function label(): string
    {
        return match ($this) {
            self::EXPIRED => __('SSL Expired'),
            self::VALID => __('SSL Valid'),
            self::NOT_YET_VALID => __('SSL Not Yet Valid'),
            self::UNCHECKED => __('SSL Unchecked'),
            self::FAILED_PARSE => __('SSL Failed to Parse'),
            self::FAILED_CHECK => __('SSL Failed to Check'),
            default => throw new \Exception('Unknown enum value requested for the label'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::EXPIRED => __('SSL for the site is expired.'),
            self::VALID => __('SSL certificate is valid.'),
            self::NOT_YET_VALID => __('SSL certificate not yet valid.'),
            self::UNCHECKED => __('Unable to connect for SSL check.'),
            self::FAILED_PARSE => __('Failed to parse SSL expiration date.'),
            self::FAILED_CHECK => __('Failed to check SSL expiration date.'),
            default => throw new \Exception('Unknown enum value requested for the description'),
        };
    }
}
