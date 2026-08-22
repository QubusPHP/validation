<?php

declare(strict_types=1);

namespace Qubus\Validation;

use Qubus\Exception\Data\TypeException;

class Attribute
{
    protected array $rules = [];

    protected string $key = '';

    protected ?string $alias = null;

    protected ?Validation $validation = null;

    protected bool $required = false;

    protected ?Attribute $primaryAttribute = null;

    protected array $otherAttributes = [];

    protected array $keyIndexes = [];

    /**
     * @param Validation  $validation
     * @param string      $key
     * @param string|null $alias
     * @param array       $rules
     * @return void
     */
    public function __construct(
        Validation $validation,
        string $key,
        ?string $alias = null,
        array $rules = []
    ) {
        $this->validation = $validation;
        $this->alias = $alias;
        $this->key = $key;
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }

    /**
     * Set the primary attribute.
     *
     * @param Attribute $primaryAttribute
     * @return void
     */
    public function setPrimaryAttribute(Attribute $primaryAttribute): void
    {
        $this->primaryAttribute = $primaryAttribute;
    }

    /**
     * Set key indexes.
     *
     * @param array $keyIndexes
     * @return void
     */
    public function setKeyIndexes(array $keyIndexes): void
    {
        $this->keyIndexes = $keyIndexes;
    }

    /**
     * Get primary attributes.
     *
     * @return Attribute|null
     */
    public function getPrimaryAttribute(): ?Attribute
    {
        return $this->primaryAttribute;
    }

    /**
     * Set other attributes.
     *
     * @param array $otherAttributes
     * @return void
     */
    public function setOtherAttributes(array $otherAttributes): void
    {
        $this->otherAttributes = [];
        foreach ($otherAttributes as $otherAttribute) {
            $this->addOtherAttribute($otherAttribute);
        }
    }

    /**
     * Add other attributes.
     *
     * @param Attribute $otherAttribute
     * @return void
     */
    public function addOtherAttribute(Attribute $otherAttribute): void
    {
        $this->otherAttributes[] = $otherAttribute;
    }

    /**
     * Get other attributes.
     *
     * @return array
     */
    public function getOtherAttributes(): array
    {
        return $this->otherAttributes;
    }

    /**
     * Add rule.
     *
     * @param Rule $rule
     * @return void
     */
    public function addRule(Rule $rule): void
    {
        $rule->setAttribute($this);
        $rule->setValidation($this->validation);

        $this->rules[$rule->getKey()] = $rule;
    }

    /**
     * Get rule.
     *
     * @param string $ruleKey
     * @return Rule|null
     */
    public function getRule(string $ruleKey)
    {
        return $this->hasRule($ruleKey) ? $this->rules[$ruleKey] : null;
    }

    /**
     * Get rules.
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Check the $ruleKey has in the rule.
     *
     * @param string $ruleKey
     * @return bool
     */
    public function hasRule(string $ruleKey): bool
    {
        return isset($this->rules[$ruleKey]);
    }

    /**
     * Set required.
     *
     * @param boolean $required
     * @return void
     */
    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    /**
     * Set rule is required.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get key indexes.
     *
     * @return array
     */
    public function getKeyIndexes(): array
    {
        return $this->keyIndexes;
    }

    /**
     * Get value.
     *
     * @param string|null $key
     * @return mixed
     * @throws TypeException
     */
    public function getValue(?string $key = null): mixed
    {
        if ($key && $this->isArrayAttribute()) {
            $key = $this->resolveSiblingKey($key);
        }

        if (!$key) {
            $key = $this->getKey();
        }

        return $this->validation->getValue($key);
    }

    /**
     * Get that is array attribute.
     *
     * @return bool
     */
    public function isArrayAttribute(): bool
    {
        return count($this->getKeyIndexes()) > 0;
    }

    /**
     * Check this attribute is using dot notation.
     *
     * @return bool
     */
    public function isUsingDotNotation(): bool
    {
        return str_contains($this->getKey(), '.');
    }

    /**
     * Resolve sibling key.
     *
     * @param string $key
     * @return string
     */
    public function resolveSiblingKey(string $key): string
    {
        $indexes = $this->getKeyIndexes();
        $index = 0;

        $resolvedKey = preg_replace_callback('/\*/', function () use ($indexes, &$index): string {
            return (string) ($indexes[$index++] ?? '*');
        }, $key);

        return $resolvedKey ?? $key;
    }

    /**
     * Get humanize key.
     *
     * @return string
     */
    public function getHumanizedKey(): string
    {
        $primaryAttribute = $this->getPrimaryAttribute();
        $key = str_replace('_', ' ', $this->key);

        // Resolve key from array validation
        if ($primaryAttribute) {
            $split = explode('.', $key);
            $key = implode(' ', array_map(function ($word) {
                if (is_numeric($word)) {
                    $word = $word + 1;
                }
                return Helper::snakeCase((string) $word, ' ');
            }, $split));
        }

        return ucfirst($key);
    }

    /**
     * Set alias.
     *
     * @param string $alias
     * @return void
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * Get alias.
     *
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }
}
