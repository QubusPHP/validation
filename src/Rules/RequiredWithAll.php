<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\MissingRequiredParameterException;
use Qubus\Validation\Rule;
use Qubus\Validation\RuleNotFoundException;

class RequiredWithAll extends Required
{
    protected bool $implicit = true;

    protected string $message = "The :attribute is required";

    /**
     * Given $params and assign $this->params
     *
     * @param array $params
     * @return self
     */
    public function fillParameters(array $params): Rule
    {
        $this->params['fields'] = $params;
        return $this;
    }

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     * @throws RuleNotFoundException
     * @throws MissingRequiredParameterException
     */
    public function check(mixed $value): bool
    {
        $this->requireParameters(['fields']);
        $fields = $this->parameter('fields');
        $validator = $this->validation->getValidator();
        $requiredValidator = $validator('required');

        foreach ($fields as $field) {
            if (!$this->validation->hasValue($field)) {
                return true;
            }
        }

        $this->setAttributeAsRequired();
        return $requiredValidator->check($value, []);
    }
}
