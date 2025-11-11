<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Exception;
use Qubus\Exception\Data\TypeException;
use Qubus\Validation\Rule;
use Closure;

use function sprintf;

class Callback extends Rule
{
    protected string $message = "The :attribute is not valid";

    protected array $fillableParams = ['callback'];

    /**
     * Set the Callback closure
     *
     * @param Closure $callback
     * @return self
     */
    public function setCallback(Closure $callback): Rule
    {
        return $this->setParameter('callback', $callback);
    }

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

        $callback = $this->parameter('callback');
        if (false === $callback instanceof Closure) {
            $key = $this->attribute->getKey();
            throw new TypeException(sprintf("Callback rule for '%s' is not callable.", $key));
        }

        $callback = $callback->bindTo($this);
        $invalidMessage = $callback($value);

        if (is_string($invalidMessage)) {
            $this->setMessage($invalidMessage);
            return false;
        } elseif (false === $invalidMessage) {
            return false;
        }

        return true;
    }
}
