<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Exception\Data\TypeException;
use Qubus\Validation\MissingRequiredParameterException;
use Qubus\Validation\Rule;
use Qubus\Validation\RuleNotFoundException;

class RequiredIf extends Required
{
    protected bool $implicit = true;

    protected string $message = "The :attribute is required";

    /**
     * Given $params and assign the $this->params
     *
     * @param array $params
     * @return self
     */
    public function fillParameters(array $params): Rule
    {
        $this->params['field'] = array_shift($params);
        $this->params['values'] = $params;
        return $this;
    }

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     * @throws RuleNotFoundException
     * @throws MissingRequiredParameterException
     * @throws TypeException
     */
    public function check(mixed $value): bool
    {
        $this->requireParameters(['field', 'values']);

        $anotherAttribute = $this->parameter('field');
        $definedValues = $this->parameter('values');
        $anotherValue = $this->getAttribute()->getValue($anotherAttribute);

        $validator = $this->validation->getValidator();
        $requiredValidator = $validator('required');

        if (in_array($anotherValue, $definedValues)) {
            $this->setAttributeAsRequired();
            return $requiredValidator->check($value);
        }

        return true;
    }
}
