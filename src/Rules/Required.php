<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;
use Qubus\Validation\Rules\Traits\FileAware;

use function Qubus\Support\Helpers\is_null__;

use const UPLOAD_ERR_NO_FILE;

class Required extends Rule
{
    use FileAware;

    protected bool $implicit = true;

    protected string $message = "The :attribute is required";

    /**
     * Check the $value is valid.
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        $this->setAttributeAsRequired();

        if ($this->attribute and $this->attribute->hasRule('uploaded_file')) {
            return $this->isValueFromUploadedFiles($value) and $value['error'] != UPLOAD_ERR_NO_FILE;
        }

        if (is_string($value)) {
            return mb_strlen(trim($value), 'UTF-8') > 0;
        }
        if (is_array($value)) {
            return count($value) > 0;
        }
        return !is_null__($value);
    }

    /**
     * Set attribute is required if $this->attribute is set.
     *
     * @return void
     */
    protected function setAttributeAsRequired(): void
    {
        $this->attribute?->setRequired(true);
    }
}
