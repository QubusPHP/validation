<?php

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
        return (strtotime($date) !== false);
    }

    /**
     * Throw exception.
     *
     * @param mixed $value
     * @return Exception
     */
    protected function throwException(mixed $value): Exception
    {
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
        return strtotime($date);
    }
}
