<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules\Traits;

use Exception;

use function sprintf;

trait DateUtilsAware
{
    /**
     * Check the $date is valid.
     *
     * @param mixed $date
     * @return bool
     */
    protected function isValidDate(mixed $date): bool
    {
        return (is_string($date) || is_int($date)) && strtotime((string) $date) !== false;
    }

    /**
     * Throw exception.
     *
     * @param mixed $value
     * @return Exception
     */
    protected function throwException(mixed $value): Exception
    {
        $value = is_scalar($value) || $value === null
        ? (string) $value
        : get_debug_type($value);

        // phpcs:ignore
        return new Exception(
            sprintf("Expected a valid date, got '%s' instead. 
            2016-12-08, 2016-12-02 14:58, tomorrow are considered valid dates", $value)
        );
    }

    /**
     * Given $date and get the time stamp
     *
     * @param mixed $date
     * @return int
     */
    protected function getTimeStamp(mixed $date): int
    {
        $timestamp = strtotime((string) $date);
        if ($timestamp === false) {
            throw $this->throwException($date);
        }

        return $timestamp;
    }
}
