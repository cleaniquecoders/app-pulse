<?php

namespace CleaniqueCoders\AppPulse\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;
use CleaniqueCoders\Traitify\Contracts\Enum as Contract;

/**
 * Enum SslStatus
 *
 * Represents the possible SSL statuses for a site, providing labels and descriptions for each status.
 */
enum SslStatus: string implements Contract
{
    use InteractsWithEnum;

    /**
     * SSL certificate is expired.
     */
    case EXPIRED = 'ssl_expired';

    /**
     * SSL certificate is valid.
     */
    case VALID = 'ssl_valid';

    /**
     * SSL certificate is not yet valid.
     */
    case NOT_YET_VALID = 'ssl_not_yet_valid';

    /**
     * SSL check has not been performed.
     */
    case UNCHECKED = 'ssl_unchecked';

    /**
     * Failed to parse SSL certificate data.
     */
    case FAILED_PARSE = 'ssl_failed_parse';

    /**
     * Failed to check SSL certificate status.
     */
    case FAILED_CHECK = 'ssl_failed_check';

    /**
     * Get the label for the SSL status.
     */
    public function label(): string
    {
        return match ($this) {
            self::EXPIRED => __('SSL Expired'),
            self::VALID => __('SSL Valid'),
            self::NOT_YET_VALID => __('SSL Not Yet Valid'),
            self::UNCHECKED => __('SSL Unchecked'),
            self::FAILED_PARSE => __('SSL Failed to Parse'),
            self::FAILED_CHECK => __('SSL Failed to Check'),
        };
    }

    /**
     * Get a description for the SSL status.
     */
    public function description(): string
    {
        return match ($this) {
            self::EXPIRED => __('SSL for the site is expired.'),
            self::VALID => __('SSL certificate is valid.'),
            self::NOT_YET_VALID => __('SSL certificate not yet valid.'),
            self::UNCHECKED => __('Unable to connect for SSL check.'),
            self::FAILED_PARSE => __('Failed to parse SSL expiration date.'),
            self::FAILED_CHECK => __('Failed to check SSL expiration date.'),
        };
    }
}
