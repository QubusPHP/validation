<?php

declare(strict_types=1);

namespace Qubus\Validation;

use function sprintf;

abstract class Rule
{
    protected ?string $key = null;

    protected ?Attribute $attribute = null;

    protected ?Validation $validation = null;

    protected bool $implicit = false;

    protected array $params = [];

    protected array $paramsTexts = [];

    protected array $fillableParams = [];

    protected string $message = "The :attribute is invalid";

    abstract public function check(mixed $value): bool;

    /**
     * Set Validation class instance
     *
     * @param Validation $validation
     * @return void
     */
    public function setValidation(Validation $validation): void
    {
        $this->validation = $validation;
    }

    /**
     * Set key
     *
     * @param string $key
     * @return void
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key ?: get_class($this);
    }

    /**
     * Set attribute
     *
     * @param Attribute $attribute
     * @return void
     */
    public function setAttribute(Attribute $attribute): void
    {
        $this->attribute = $attribute;
    }

    /**
     * Get attribute
     *
     * @return Attribute|null
     */
    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->params;
    }

    /**
     * Set params
     *
     * @param array $params
     * @return Rule
     */
    public function setParameters(array $params): Rule
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Set parameters
     *
     * @param string $key
     * @param mixed $value
     * @return Rule
     */
    public function setParameter(string $key, mixed $value): Rule
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Fill $params to $this->params
     *
     * @param array $params
     * @return Rule
     */
    public function fillParameters(array $params): Rule
    {
        foreach ($this->fillableParams as $key) {
            if (empty($params)) {
                break;
            }
            $this->params[$key] = array_shift($params);
        }
        return $this;
    }

    /**
     * Get parameter from given $key, return null if it not exists
     *
     * @param string $key
     * @return mixed
     */
    public function parameter(string $key): mixed
    {
        return $this->params[$key] ?? null;
    }

    /**
     * Set parameter text that can be displayed in error message using ':param_key'
     *
     * @param string $key
     * @param string $text
     * @return void
     */
    public function setParameterText(string $key, string $text): void
    {
        $this->paramsTexts[$key] = $text;
    }

    /**
     * Get $paramsTexts
     *
     * @return array
     */
    public function getParametersTexts(): array
    {
        return $this->paramsTexts;
    }

    /**
     * Check whether this rule is implicit
     *
     * @return boolean
     */
    public function isImplicit(): bool
    {
        return $this->implicit;
    }

    /**
     * Just alias of setMessage
     *
     * @param string $message
     * @return Rule
     */
    public function message(string $message): Rule
    {
        return $this->setMessage($message);
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Rule
     */
    public function setMessage(string $message): Rule
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Check given $params must exist.
     *
     * @param array $params
     * @return void
     * @throws MissingRequiredParameterException
     */
    protected function requireParameters(array $params): void
    {
        foreach ($params as $param) {
            if (!isset($this->params[$param])) {
                $rule = $this->getKey();
                throw new MissingRequiredParameterException(
                    sprintf("Missing required parameter '%s' on rule '%s'", $param, $rule)
                );
            }
        }
    }
}
