<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;

class Present extends Rule
{
    protected bool $implicit = true;

    protected string $message = "The :attribute must be present";

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        $this->setAttributeAsRequired();

        return $this->validation->hasValue($this->attribute->getKey());
    }

    /**
     * Set attribute is required if $this->attribute is set
     *
     * @return void
     */
    protected function setAttributeAsRequired(): void
    {
        $this->attribute?->setRequired(true);
    }
}
