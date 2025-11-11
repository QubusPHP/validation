<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Exception;
use Qubus\Validation\Rule;
use Qubus\Validation\Rules\Traits\DateUtilsAware;

class Before extends Rule
{
    use DateUtilsAware;

    protected string $message = "The :attribute must be a date before :time.";

    protected array $fillableParams = ['time'];

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     * @throws Exception
     */
    public function check(mixed $value): bool
    {
        $this->requireParameters($this->fillableParams);
        $time = $this->parameter('time');

        if (!$this->isValidDate($value)) {
            throw $this->throwException($value);
        }

        if (!$this->isValidDate($time)) {
            throw $this->throwException($time);
        }

        return $this->getTimeStamp($time) > $this->getTimeStamp($value);
    }
}
