<?php

declare(strict_types=1);

namespace Qubus\Validation;

use Closure;
use Exception;
use Qubus\Exception\Data\TypeException;
use Qubus\Validation\Rules\Interfaces\BeforeValidate;
use Qubus\Validation\Rules\Interfaces\ModifyValue;
use Qubus\Validation\Rules\Required;
use Qubus\Validation\Traits\MessagesAware;
use Qubus\Validation\Traits\TranslationsAware;

use function sprintf;

class Validation
{
    use TranslationsAware;
    use MessagesAware;

    protected mixed $validator;

    protected array $inputs = [];

    protected array $attributes = [];

    protected array $aliases = [];

    protected string $messageSeparator = ':';

    protected array $validData = [];

    protected array $invalidData = [];

    public ?ErrorBag $errors = null;

    /**
     * @param Validator $validator
     * @param array $inputs
     * @param array $rules
     * @param array $messages
     * @return void
     * @throws Exception
     */
    public function __construct(
        Validator $validator,
        array $inputs,
        array $rules,
        array $messages = []
    ) {
        $this->validator = $validator;
        $this->inputs = $this->resolveInputAttributes($inputs);
        $this->messages = $messages;
        $this->errors = new ErrorBag();
        foreach ($rules as $attributeKey => $arrayRules) {
            $this->addAttribute($attributeKey, $arrayRules);
        }
    }

    /**
     * Add attribute rules.
     *
     * @param string $attributeKey
     * @param array|string $rules
     * @return void
     * @throws Exception
     */
    public function addAttribute(string $attributeKey, array|string $rules): void
    {
        $resolvedRules = $this->resolveRules($rules);
        $attribute = new Attribute($this, $attributeKey, $this->getAlias($attributeKey), $resolvedRules);
        $this->attributes[$attributeKey] = $attribute;
    }

    /**
     * Get attribute by key.
     *
     * @param string $attributeKey
     * @return null|Attribute
     */
    public function getAttribute(string $attributeKey): ?Attribute
    {
        return $this->attributes[$attributeKey] ?? null;
    }

    /**
     * Run validation.
     *
     * @param array $inputs
     * @return void
     * @throws MissingRequiredParameterException
     * @throws TypeException
     */
    public function validate(array $inputs = []): void
    {
        $this->errors = new ErrorBag(); // reset error bag
        $this->validData = [];
        $this->invalidData = [];
        $this->inputs = array_replace($this->inputs, $this->resolveInputAttributes($inputs));

        foreach ($this->attributes as $attribute) {
            $attribute->setRequired(false);
        }

        // Before validation hooks
        foreach ($this->attributes as $attribute) {
            foreach ($attribute->getRules() as $rule) {
                $rule->setAttribute($attribute);
                if ($rule instanceof BeforeValidate) {
                    $rule->beforeValidate();
                }
            }
        }

        foreach ($this->attributes as $attribute) {
            $this->validateAttribute($attribute);
        }
    }

    /**
     * Get ErrorBag instance.
     *
     * @return ErrorBag
     */
    public function errors(): ErrorBag
    {
        return $this->errors;
    }

    /**
     * Validate attribute.
     *
     * @param Attribute $attribute
     * @return void
     * @throws MissingRequiredParameterException
     * @throws TypeException
     */
    protected function validateAttribute(Attribute $attribute): void
    {
        if ($this->isArrayAttribute($attribute)) {
            $attributes = $this->parseArrayAttribute($attribute);
            foreach ($attributes as $i => $attr) {
                $this->validateAttribute($attr);
            }
            return;
        }

        $attributeKey = $attribute->getKey();
        $rules = $attribute->getRules();

        $value = $this->getValue($attributeKey);
        $isEmptyValue = $this->isEmptyValue($value);

        if ($attribute->hasRule('nullable') && $isEmptyValue) {
            $rules = [];
        }

        $isValid = true;
        foreach ($rules as $ruleValidator) {
            $ruleValidator->setAttribute($attribute);

            if ($ruleValidator instanceof ModifyValue) {
                $value = $ruleValidator->modifyValue($value);
                $isEmptyValue = $this->isEmptyValue($value);
            }

            $valid = $ruleValidator->check($value);

            if ($isEmptyValue && $this->ruleIsOptional($attribute, $ruleValidator)) {
                continue;
            }

            if (!$valid) {
                $isValid = false;
                $this->addError($attribute, $value, $ruleValidator);
                if ($ruleValidator->isImplicit()) {
                    break;
                }
            }
        }

        if ($isValid) {
            $this->setValidData($attribute, $value);
        } else {
            $this->setInvalidData($attribute, $value);
        }
    }

    /**
     * Check whether given $attribute is array attribute.
     *
     * @param Attribute $attribute
     * @return bool
     */
    protected function isArrayAttribute(Attribute $attribute): bool
    {
        $key = $attribute->getKey();
        return str_contains($key, '*');
    }

    /**
     * Parse array attribute into it's child attributes.
     *
     * @param Attribute $attribute
     * @return array
     */
    protected function parseArrayAttribute(Attribute $attribute): array
    {
        $attributeKey = $attribute->getKey();
        $data = Helper::arrayDot($this->initializeAttributeOnData($attributeKey));

        $pattern = str_replace('\*', '([^\.]+)', preg_quote($attributeKey));

        $data = array_replace($data, $this->extractValuesForWildcards(
            $data,
            $attributeKey
        ));

        $attributes = [];
        foreach ($data as $key => $value) {
            $key = (string) $key;
            if ((bool) preg_match('/^' . $pattern . '\z/', $key, $match)) {
                $attr = new Attribute($this, $key, null, $attribute->getRules());
                $attr->setPrimaryAttribute($attribute);
                $attr->setKeyIndexes(array_slice($match, 1));
                $attributes[] = $attr;
            }
        }

        // set other attributes to each attributes
        foreach ($attributes as $i => $attr) {
            $otherAttributes = $attributes;
            unset($otherAttributes[$i]);
            $attr->setOtherAttributes($otherAttributes);
        }

        return $attributes;
    }

    /**
     * Gather a copy of the attribute data filled with any missing attributes.
     *
     * @param string $attributeKey
     * @return array
     */
    protected function initializeAttributeOnData(string $attributeKey): array
    {
        $explicitPath = $this->getLeadingExplicitAttributePath($attributeKey);

        $data = $this->extractDataFromPath($explicitPath);

        $asteriskPos = strpos($attributeKey, '*');

        if (false === $asteriskPos || $asteriskPos === (mb_strlen($attributeKey, 'UTF-8') - 1)) {
            return $data;
        }

        return Helper::arraySet($data, $attributeKey, null, true);
    }

    /**
     * Get all the exact attribute values for a given wildcard attribute.
     *
     * @param array $data
     * @param string $attributeKey
     * @return array
     */
    public function extractValuesForWildcards(array $data, string $attributeKey): array
    {
        $keys = [];

        $pattern = str_replace('\*', '[^\.]+', preg_quote($attributeKey));

        foreach ($data as $key => $value) {
            $key = (string) $key;
            if ((bool) preg_match('/^' . $pattern . '/', $key, $matches)) {
                $keys[] = $matches[0];
            }
        }

        $keys = array_unique($keys);

        $data = [];

        foreach ($keys as $key) {
            $data[$key] = Helper::arrayGet($this->inputs, $key);
        }

        return $data;
    }

    /**
     * Get the explicit part of the attribute name.
     *
     * E.g. 'foo.bar.*.baz' -> 'foo.bar'
     *
     * Allows us to not spin through all the flattened data for some operations.
     *
     * @param  string  $attributeKey
     * @return string|null null when root wildcard
     */
    protected function getLeadingExplicitAttributePath(string $attributeKey): ?string
    {
        return rtrim(explode('*', $attributeKey)[0], '.') ?: null;
    }

    /**
     * Extract data based on the given dot-notated path.
     *
     * Used to extract a subsection of the data for faster iteration.
     *
     * @param string|null $attributeKey
     * @return array
     */
    protected function extractDataFromPath(?string $attributeKey): array
    {
        $results = [];
        $missing = new \stdClass();

        $value = Helper::arrayGet($this->inputs, $attributeKey, $missing);

        if ($value !== $missing) {
            Helper::arraySet($results, $attributeKey, $value);
        }

        return $results;
    }

    /**
     * Add error to the $this->errors.
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @param Rule $ruleValidator
     * @return void
     */
    protected function addError(Attribute $attribute, mixed $value, Rule $ruleValidator): void
    {
        $ruleName = $ruleValidator->getKey();
        $message = $this->resolveMessage($attribute, $value, $ruleValidator);

        $this->errors->add($attribute->getKey(), $ruleName, $message);
    }

    /**
     * Check $value is empty value.
     *
     * @param mixed $value
     * @return boolean
     */
    protected function isEmptyValue(mixed $value): bool
    {
        $requiredValidator = new Required();
        return false === $requiredValidator->check($value);
    }

    /**
     * Check the rule is optional.
     *
     * @param Attribute $attribute
     * @param Rule $rule
     * @return bool
     */
    protected function ruleIsOptional(Attribute $attribute, Rule $rule): bool
    {
        return false === $attribute->isRequired() and
        false === $rule->isImplicit() and
        false === $rule instanceof Required;
    }

    /**
     * Resolve attribute name.
     *
     * @param Attribute $attribute
     * @return string
     */
    protected function resolveAttributeName(Attribute $attribute): string
    {
        $primaryAttribute = $attribute->getPrimaryAttribute();
        if (isset($this->aliases[$attribute->getKey()])) {
            return $this->aliases[$attribute->getKey()];
        } elseif ($primaryAttribute and isset($this->aliases[$primaryAttribute->getKey()])) {
            return $this->aliases[$primaryAttribute->getKey()];
        } elseif ($this->validator->isUsingHumanizedKey()) {
            return $attribute->getHumanizedKey();
        } else {
            return $attribute->getKey();
        }
    }

    /**
     * Resolve message.
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @param Rule $validator
     * @return string
     */
    protected function resolveMessage(Attribute $attribute, mixed $value, Rule $validator): string
    {
        $primaryAttribute = $attribute->getPrimaryAttribute();
        $params = array_merge($validator->getParameters(), $validator->getParametersTexts());
        $attributeKey = $attribute->getKey();
        $ruleKey = $validator->getKey();
        $alias = $attribute->getAlias() ?: $this->resolveAttributeName($attribute);
        $message = $validator->getMessage(); // default rule message
        $messageKeys = [
            $attributeKey . $this->messageSeparator . $ruleKey,
            $attributeKey,
            $ruleKey
        ];

        if ($primaryAttribute) {
            // insert primaryAttribute keys
            // $messageKeys = [
            //     $attributeKey.$this->messageSeparator.$ruleKey,
            //     >> here [1] <<
            //     $attributeKey,
            //     >> and here [3] <<
            //     $ruleKey
            // ];
            $primaryAttributeKey = $primaryAttribute->getKey();
            array_splice($messageKeys, 1, 0, $primaryAttributeKey . $this->messageSeparator . $ruleKey);
            array_splice($messageKeys, 3, 0, $primaryAttributeKey);
        }

        foreach ($messageKeys as $key) {
            if (isset($this->messages[$key])) {
                $message = $this->messages[$key];
                break;
            }
        }

        // Replace message params
        $vars = array_merge($params, [
            'attribute' => $alias,
            'value' => $value,
        ]);

        foreach ($vars as $key => $value) {
            $value = $this->stringify($value);
            $message = str_replace(':' . $key, $value, $message);
        }

        // Replace key indexes
        $keyIndexes = $attribute->getKeyIndexes();
        foreach ($keyIndexes as $pathIndex => $index) {
            $replacers = [
                "[{$pathIndex}]" => $index,
            ];

            if (is_numeric($index)) {
                $replacers["{{$pathIndex}}"] = $index + 1;
            }

            $message = str_replace(array_keys($replacers), array_values($replacers), $message);
        }

        return $message;
    }

    /**
     * Stringify $value.
     *
     * @param mixed $value
     * @return string
     */
    protected function stringify(mixed $value): string
    {
        if (is_string($value) || is_numeric($value)) {
            return (string) $value;
        } elseif (is_array($value) || is_object($value)) {
            return (string) json_encode($value);
        } else {
            return '';
        }
    }

    /**
     * Resolve $rules.
     *
     * @param mixed $rules
     * @return array
     * @throws Exception
     */
    protected function resolveRules(mixed $rules): array
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        if (!is_array($rules)) {
            throw new Exception('Rules must be a string or an array.');
        }

        $resolvedRules = [];
        $validatorFactory = $this->getValidator();

        foreach ($rules as $i => $rule) {
            if (empty($rule)) {
                continue;
            }
            $params = [];

            if (is_string($rule)) {
                [$rulename, $params] = $this->parseRule($rule);
                $validator = call_user_func_array($validatorFactory, array_merge([$rulename], $params));
            } elseif ($rule instanceof Rule) {
                $validator = $rule;
            } elseif ($rule instanceof Closure) {
                $validator = call_user_func_array($validatorFactory, ['callback', $rule]);
            } else {
                $ruleName = is_object($rule) ? get_class($rule) : gettype($rule);
                $message = sprintf(
                    "Rule must be a string, Closure or '%s' instance. %s given",
                    Rule::class,
                    $ruleName
                );
                throw new Exception($message);
            }

            $resolvedRules[] = $validator;
        }

        return $resolvedRules;
    }

    /**
     * Parse $rule.
     *
     * @param string $rule
     * @return array
     */
    protected function parseRule(string $rule): array
    {
        $exp = explode(':', $rule, 2);
        $rulename = $exp[0];
        if ($rulename !== 'regex') {
            $params = isset($exp[1]) ? explode(',', $exp[1]) : [];
        } else {
            $params = isset($exp[1]) ? [$exp[1]] : [];
        }

        return [$rulename, $params];
    }

    /**
     * Given $attributeKey and $alias then assign alias.
     *
     * @param string $attributeKey
     * @param string $alias
     * @return void
     */
    public function setAlias(string $attributeKey, string $alias): void
    {
        $this->aliases[$attributeKey] = $alias;
    }

    /**
     * Get attribute alias from given key.
     *
     * @param string $attributeKey
     * @return string|null
     */
    public function getAlias(string $attributeKey): ?string
    {
        return $this->aliases[$attributeKey] ?? null;
    }

    /**
     * Set attributes aliases.
     *
     * @param array $aliases
     * @return void
     */
    public function setAliases(array $aliases): void
    {
        $this->aliases = array_merge($this->aliases, $aliases);
    }

    /**
     * Check validations are passed.
     *
     * @return bool
     */
    public function passes(): bool
    {
        return $this->errors->count() == 0;
    }

    /**
     * Check validations are failed.
     *
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Given $key and get value.
     *
     * @param string $key
     * @return mixed
     */
    public function getValue(string $key): mixed
    {
        return Helper::arrayGet($this->inputs, $key);
    }

    /**
     * Set input value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setValue(string $key, mixed $value): void
    {
        Helper::arraySet($this->inputs, $key, $value);
    }

    /**
     * Given $key and check value exists.
     *
     * @param string $key
     * @return bool
     */
    public function hasValue(string $key): bool
    {
        return Helper::arrayHas($this->inputs, $key);
    }

    /**
     * Get Validator class instance.
     *
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return $this->validator;
    }

    /**
     * Given $inputs and resolve input attributes.
     *
     * @param array $inputs
     * @return array
     */
    protected function resolveInputAttributes(array $inputs): array
    {
        $resolvedInputs = [];
        foreach ($inputs as $key => $rules) {
            $exp = explode(':', (string) $key, 2);

            if (count($exp) > 1) {
                // set attribute alias
                $this->aliases[$exp[0]] = $exp[1];
            }

            $resolvedInputs[$exp[0]] = $rules;
        }

        return $resolvedInputs;
    }

    /**
     * Get validated data.
     *
     * @return array
     */
    public function getValidatedData(): array
    {
        return array_replace_recursive($this->validData, $this->invalidData);
    }

    /**
     * Set valid data.
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return void
     */
    protected function setValidData(Attribute $attribute, mixed $value): void
    {
        $key = $attribute->getKey();
        if ($attribute->isArrayAttribute() || $attribute->isUsingDotNotation()) {
            Helper::arraySet($this->validData, $key, $value);
            Helper::arrayUnset($this->invalidData, $key);
        } else {
            $this->validData[$key] = $value;
            unset($this->invalidData[$key]);
        }
    }

    /**
     * Get valid data.
     *
     * @return array
     */
    public function getValidData(): array
    {
        return $this->validData;
    }

    /**
     * Set invalid data.
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return void
     */
    protected function setInvalidData(Attribute $attribute, mixed $value): void
    {
        $key = $attribute->getKey();
        if ($attribute->isArrayAttribute() || $attribute->isUsingDotNotation()) {
            Helper::arraySet($this->invalidData, $key, $value);
            Helper::arrayUnset($this->validData, $key);
        } else {
            $this->invalidData[$key] = $value;
            unset($this->validData[$key]);
        }
    }

    /**
     * Get invalid data.
     *
     * @return array
     */
    public function getInvalidData(): array
    {
        return $this->invalidData;
    }
}
