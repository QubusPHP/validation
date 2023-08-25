<?php

/**
 * Qubus\Validation
 *
 * @link       https://github.com/QubusPHP/validation
 * @copyright  2020
 * @author     Joshua Parker <joshua@joshuaparker.dev>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace Qubus\Validation;

use BadMethodCallException;
use Closure;
use DateTime;
use DateTimeZone;
use Qubus\Exception\Data\TypeException;
use Qubus\Exception\Exception;
use Qubus\Validation\Interfaces\PresenceVerifier;
use Qubus\Validation\Translators\StringTranslator;
use RuntimeException;

use function array_combine;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function array_slice;
use function array_values;
use function call_user_func;
use function call_user_func_array;
use function checkdate;
use function checkdnsrr;
use function count;
use function date_parse;
use function date_parse_from_format;
use function explode;
use function filter_var;
use function func_get_args;
use function function_exists;
use function implode;
use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function is_uploaded_file;
use function mb_strlen;
use function method_exists;
use function pathinfo;
use function preg_match;
use function Qubus\Support\Helpers\array_dot;
use function Qubus\Support\Helpers\is_false__;
use function Qubus\Support\Helpers\is_null__;
use function Qubus\Support\Helpers\return_array;
use function Qubus\Support\Helpers\snake_case;
use function Qubus\Support\Helpers\studly_case;
use function str_getcsv;
use function str_replace;
use function strlen;
use function strpos;
use function strtolower;
use function strtotime;
use function strtoupper;
use function substr;
use function trim;
use function ucfirst;

use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_EMAIL;
use const FILTER_VALIDATE_INT;
use const FILTER_VALIDATE_IP;
use const FILTER_VALIDATE_URL;
use const PATHINFO_EXTENSION;

class Validator implements Validatable
{
    /**
     * The StringTranslator implementation.
     */
    protected StringTranslator $translator;

    /**
     * The Presence Verifier implementation.
     */
    protected ?PresenceVerifier $presenceVerifier;

    /**
     * The failed validation rules.
     *
     * @var array $failedRules
     */
    protected array $failedRules = [];

    /**
     * The messages.
     */
    protected ?MessageBag $messages = null;

    /**
     * The data under validation.
     *
     * @var array $data
     */
    protected array $data;

    /**
     * The files under validation.
     *
     * @var array $files
     */
    protected array $files = [];

    /**
     * The rules to be applied to the data.
     *
     * @var array $rules
     */
    protected array $rules;

    /**
     * All the registered "after" callbacks.
     *
     * @var array $after
     */
    protected array $after = [];

    /**
     * The array of custom error messages.
     *
     * @var array $customMessages
     */
    protected array $customMessages = [];

    /**
     * The array of fallback error messages.
     *
     * @var array $fallbackMessages
     */
    protected array $fallbackMessages = [];

    /**
     * The array of custom attribute names.
     *
     * @var array $customAttributes
     */
    protected array $customAttributes = [];

    /**
     * The array of custom displayable values.
     *
     * @var array $customValues
     */
    protected array $customValues = [];

    /**
     * All the custom validator extensions.
     *
     * @var array $extensions
     */
    protected array $extensions = [];

    /**
     * All the custom replacer extensions.
     *
     * @var array $replacers
     */
    protected array $replacers = [];

    /**
     * The size related validation rules.
     *
     * @var array $sizeRules
     */
    protected array $sizeRules = ['Size', 'Between', 'Min', 'Max'];

    /**
     * The numeric related validation rules.
     *
     * @var array $numericRules
     */
    protected array $numericRules = ['Numeric', 'Integer'];

    /**
     * The validation rules that imply the field is required.
     *
     * @var array $implicitRules
     */
    protected array $implicitRules = [
        'Required',
        'Filled',
        'RequiredWith',
        'RequiredWithAll',
        'RequiredWithout',
        'RequiredWithoutAll',
        'RequiredIf',
        'Accepted',
    ];

    /**
     * Create a new Validator instance.
     *
     * @param StringTranslator $translator
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     */
    public function __construct(
        StringTranslator $translator,
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ) {
        $this->translator = $translator;
        $this->customMessages = $messages;
        $this->data = $this->parseData($data);
        $this->rules = $this->explodeRules($rules);
        $this->customAttributes = $customAttributes;
    }

    /**
     * Parse the data and hydrate the files array.
     *
     * @param array $data
     * @return array
     */
    protected function parseData(array $data): array
    {
        $this->files = [];

        foreach ($data as $key => $value) {
            // If this value is an instance of the Qubus\Http File class we will
            // remove it from the data array and add it to the files array, which
            // we use to conveniently separate out these files from other data.
            if (in_array($value, $_FILES, true)) {
                $this->files[$key] = $value;

                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Explode the rules into an array of rules.
     *
     * @param array|string $rules
     * @return array
     */
    protected function explodeRules(array|string $rules): array
    {
        foreach ($rules as $key => &$rule) {
            $rule = is_string($rule) ? explode('|', $rule) : $rule;
        }

        return $rules;
    }

    /**
     * After an after validation callback.
     *
     * @param callable|string $callback
     * @return $this
     */
    public function after(callable|string $callback): static
    {
        $this->after[] = fn () => call_user_func($callback, [], 'validate');

        return $this;
    }

    /**
     * Add conditions to a given field based on a Closure.
     *
     * @param string $attribute
     * @param array|string $rules
     * @param callable $callback
     */
    public function sometimes(string $attribute, array|string $rules, callable $callback): void
    {
        $payload = array_merge($this->data, $this->files);

        if (call_user_func($callback, $payload)) {
            foreach ((array) $attribute as $key) {
                $this->mergeRules($key, $rules);
            }
        }
    }

    /**
     * Define a set of rules that apply to each element in an array attribute.
     *
     * @param string $attribute
     * @param array|string $rules
     * @throws TypeException
     */
    public function each(string $attribute, array|string $rules): void
    {
        $data = return_array($this->data, $attribute);

        if (! is_array($data)) {
            if ($this->hasRule($attribute, 'Array')) {
                return;
            }

            throw new TypeException('Attribute for each() must be an array.');
        }

        foreach ($data as $dataKey => $dataValue) {
            foreach ($rules as $ruleValue) {
                $this->mergeRules("$attribute.$dataKey", $ruleValue);
            }
        }
    }

    /**
     * Merge additional rules into a given attribute.
     *
     * @param string $attribute
     * @param array|string $rules
     */
    public function mergeRules(string $attribute, array|string $rules): void
    {
        $current = $this->rules[$attribute] ?? [];

        $merge = head($this->explodeRules([$rules]));

        $this->rules[$attribute] = array_merge($current, $merge);
    }

    /**
     * Determine if the data passes the validation rules.
     */
    public function passes(): bool
    {
        $this->messages = new MessageBag();

        // We'll spin through each rule, validating the attributes attached to that
        // rule. Any error messages will be added to the containers with each of
        // the other error messages, returning true if we don't have messages.
        foreach ($this->rules as $attribute => $rules) {
            foreach ($rules as $rule) {
                $this->validate($attribute, $rule);
            }
        }

        // Here we will spin through all of the "after" hooks on this validator and
        // fire them off. This gives the callbacks a chance to perform all kinds
        // of other validation that needs to get wrapped up in this operation.
        foreach ($this->after as $after) {
            call_user_func($after);
        }

        return count($this->messages->all()) === 0;
    }

    /**
     * Determine if the data fails the validation rules.
     */
    public function fails(): bool
    {
        return ! $this->passes();
    }

    /**
     * Validate a given attribute against a rule.
     */
    protected function validate(string $attribute, string $rule): void
    {
        [$rule, $parameters] = $this->parseRule($rule);

        if ($rule === '') {
            return;
        }

        // We will get the value for the given attribute from the array of data and then
        // verify that the attribute is indeed validatable. Unless the rule implies
        // that the attribute is required, rules are not run for missing values.
        $value = $this->getValue($attribute);

        $validatable = $this->isValidatable($rule, $attribute, $value);

        $method = "validate{$rule}";

        if ($validatable && ! $this->$method($attribute, $value, $parameters, $this)) {
            $this->addFailure($attribute, $rule, $parameters);
        }
    }

    /**
     * Get the value of a given attribute.
     *
     * @param string $attribute
     * @return array|null
     */
    protected function getValue(string $attribute): ?array
    {
        if (null !== ($value = return_array($this->data, $attribute))) {
            return $value;
        } elseif (null !== ($value = return_array($this->files, $attribute))) {
            return $value;
        }

        return null;
    }

    /**
     * Determine if the attribute is validatable.
     *
     * @param string $rule
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function isValidatable(string $rule, string $attribute, mixed $value): bool
    {
        return $this->presentOrRuleIsImplicit($rule, $attribute, $value) &&
        $this->passesOptionalCheck($attribute);
    }

    /**
     * Determine if the field is present, or the rule implies required.
     *
     * @param string $rule
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function presentOrRuleIsImplicit(string $rule, string $attribute, mixed $value): bool
    {
        return $this->validateRequired($attribute, $value) || $this->isImplicit($rule);
    }

    /**
     * Determine if the attribute passes any optional check.
     */
    protected function passesOptionalCheck(string $attribute): bool
    {
        if ($this->hasRule($attribute, ['Sometimes'])) {
            return array_key_exists($attribute, array_dot($this->data))
            || array_key_exists($attribute, $this->files);
        }

        return true;
    }

    /**
     * Determine if a given rule implies the attribute is required.
     */
    protected function isImplicit(string $rule): bool
    {
        return in_array($rule, $this->implicitRules, true);
    }

    /**
     * Add a failed rule and error message to the collection.
     *
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     */
    protected function addFailure(string $attribute, string $rule, array $parameters): void
    {
        $this->addError($attribute, $rule, $parameters);

        $this->failedRules[$attribute][snake_case($rule)] = $parameters;
    }

    /**
     * Add an error message to the validator's collection of messages.
     *
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     */
    protected function addError(string $attribute, string $rule, array $parameters): void
    {
        $message = $this->getMessage($attribute, $rule);

        $message = $this->doReplacements($message, $attribute, $rule, $parameters);

        $this->messages->add($attribute, $message);
    }

    /**
     * "Validate" optional attributes.
     *
     * Always returns true, just lets us put sometimes in rules.
     */
    protected function validateSometimes(): bool
    {
        return true;
    }

    /**
     * Validate that a required attribute exists.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateRequired(string $attribute, mixed $value): bool
    {
        if (is_null__($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif (is_array($value) && count($value) < 1) {
            return false;
        } elseif (in_array($value, $_FILES, true)) {
            return (string) $value['tmp_name'] !== '';
        }

        return true;
    }

    /**
     * Validate the given attribute is filled if it is present.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateFilled(string $attribute, mixed $value): bool
    {
        if (array_key_exists($attribute, $this->data) || array_key_exists($attribute, $this->files)) {
            return $this->validateRequired($attribute, $value);
        } else {
            return true;
        }
    }

    /**
     * Determine if any of the given attributes fail the required test.
     *
     * @param array $attributes
     * @return bool
     */
    protected function anyFailingRequired(array $attributes): bool
    {
        foreach ($attributes as $key) {
            if (! $this->validateRequired($key, $this->getValue($key))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if all the given attributes fail the required test.
     *
     * @param array $attributes
     * @return bool
     */
    protected function allFailingRequired(array $attributes): bool
    {
        foreach ($attributes as $key) {
            if ($this->validateRequired($key, $this->getValue($key))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that an attribute exists when any other attribute exists.
     *
     * @param string $attribute
     * @param mixed $value
     * @param mixed $parameters
     * @return bool
     */
    protected function validateRequiredWith(string $attribute, mixed $value, mixed $parameters): bool
    {
        if (! $this->allFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when all other attributes exists.
     *
     * @param string $attribute
     * @param mixed $value
     * @param mixed $parameters
     * @return bool
     */
    protected function validateRequiredWithAll(string $attribute, mixed $value, mixed $parameters): bool
    {
        if (! $this->anyFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute does not.
     *
     * @param string $attribute
     * @param mixed $value
     * @param mixed $parameters
     * @return bool
     */
    protected function validateRequiredWithout(string $attribute, mixed $value, array $parameters): bool
    {
        if ($this->anyFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when all other attributes do not.
     *
     * @param string $attribute
     * @param mixed $value
     * @param mixed $parameters
     * @return bool
     */
    protected function validateRequiredWithoutAll(string $attribute, mixed $value, array $parameters): bool
    {
        if ($this->allFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute has a given value.
     *
     * @param string $attribute
     * @param mixed $value
     * @param mixed $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateRequiredIf(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'required_if');

        $data = return_array($this->data, $parameters[0]);

        $values = array_slice($parameters, 1);

        if (in_array($data, $values)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Get the number of attributes in a list that are present.
     *
     * @param array $attributes
     * @return int
     */
    protected function getPresentCount(array $attributes): int
    {
        $count = 0;

        foreach ($attributes as $key) {
            if (return_array($this->data, $key) || return_array($this->files, $key)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Validate that an attribute has a matching confirmation.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     * @throws TypeException
     */
    protected function validateConfirmed(string $attribute, mixed $value): bool
    {
        return $this->validateSame($attribute, $value, [$attribute . '_confirmation']);
    }

    /**
     * Validate that two attributes match.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateSame(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'same');

        $other = return_array($this->data, $parameters[0]);

        return isset($other) && $value === $other;
    }

    /**
     * Validate that an attribute is different from another attribute.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateDifferent(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'different');

        $other = return_array($this->data, $parameters[0]);

        return isset($other) && $value !== $other;
    }

    /**
     * Validate that an attribute was "accepted".
     *
     * This validation rule implies the attribute is "required".
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateAccepted(string $attribute, mixed $value): bool
    {
        $acceptable = ['yes', 'on', '1', 1, true, 'true'];

        return $this->validateRequired($attribute, $value) && in_array($value, $acceptable, true);
    }

    /**
     * Validate that an attribute is a boolean.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateBoolean(string $attribute, mixed $value): bool
    {
        $acceptable = [true, false, 0, 1, '0', '1'];

        return in_array($value, $acceptable, true);
    }

    /**
     * Validate that an attribute is an array.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateArray(string $attribute, mixed $value): bool
    {
        return is_array($value);
    }

    /**
     * Validate that an attribute is numeric.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateNumeric(string $attribute, mixed $value): bool
    {
        return is_numeric($value);
    }

    /**
     * Validate that an attribute is an integer.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateInteger(string $attribute, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate that an attribute has a given number of digits.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateDigits(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'digits');

        return ! preg_match('/[^0-9]/', (string) $value)
        && $this->validateNumeric($attribute, $value)
        && strlen((string) $value) === (int) $parameters[0];
    }

    /**
     * Validate that an attribute is between a given number of digits.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateDigitsBetween(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'digits_between');

        $length = strlen((string) $value);

        return $length >= $parameters[0] && $length <= $parameters[1];
    }

    /**
     * Validate the size of an attribute.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateSize(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'size');

        return $this->getSize($attribute, $value) === (int) $parameters[0];
    }

    /**
     * Validate the size of an attribute is between a set of values.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateBetween(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'between');

        $size = $this->getSize($attribute, $value);

        return $size >= $parameters[0] && $size <= $parameters[1];
    }

    /**
     * Validate the size of an attribute is greater than a minimum value.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateMin(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'min');

        return $this->getSize($attribute, $value) >= $parameters[0];
    }

    /**
     * Validate the size of an attribute is less than a maximum value.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateMax(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'max');

        if (! empty($value['tmp_name']) && is_uploaded_file($value['tmp_name']) && $value['error']) {
            return false;
        }

        return $this->getSize($attribute, $value) <= $parameters[0];
    }

    /**
     * Get the size of an attribute.
     *
     * @param string $attribute
     * @param mixed $value
     * @return array|int|float|null
     */
    protected function getSize(string $attribute, mixed $value): array|int|null|float
    {
        $hasNumeric = $this->hasRule($attribute, $this->numericRules);

        // This method will determine if the attribute is a number, string, or file and
        // return the proper size accordingly. If it is a number, then number itself
        // is the size. If it is a file, we take kilobytes, and for a string the
        // entire length of the string will be considered the attribute size.
        if (is_numeric($value) && $hasNumeric) {
            return return_array($this->data, $attribute);
        } elseif (is_array($value)) {
            return count($value);
        } elseif (in_array($value, $_FILES, true)) {
            return $value->getSize() / 1024;
        } else {
            return $this->getStringSize($value);
        }
    }

    /**
     * Get the size of a string.
     *
     * @param string $value
     * @return int
     */
    protected function getStringSize($value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen((string) $value);
        }

        return strlen($value);
    }

    /**
     * Validate an attribute is contained within a list of values.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateIn(string $attribute, mixed $value, array $parameters): bool
    {
        return in_array((string) $value, $parameters, true);
    }

    /**
     * Validate an attribute is not contained within a list of values.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateNotIn(string $attribute, mixed $value, array $parameters): bool
    {
        return ! $this->validateIn($attribute, $value, $parameters);
    }

    /**
     * Validate the uniqueness of an attribute value on a given database table.
     *
     * If a database column is not specified, the attribute will be used.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateUnique(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'unique');

        $table = $parameters[0];

        // The second parameter position holds the name of the column that needs to
        // be verified as unique. If this parameter isn't specified we will just
        // assume that this column to be verified shares the attribute's name.
        $column = $parameters[1] ?? $attribute;

        [$idColumn, $id] = [null, null];

        if (isset($parameters[2])) {
            [$idColumn, $id] = $this->getUniqueIds($parameters);

            if (strtolower($id) === 'null') {
                $id = null;
            }
        }

        // The presence verifier is responsible for counting rows within this store
        // mechanism which might be a relational database or any other permanent
        // data store like Redis, etc. We will use it to determine uniqueness.
        $verifier = $this->getPresenceVerifier();

        $extra = $this->getUniqueExtra($parameters);

        return $verifier->getCount(
            $table,
            $column,
            $value,
            $id,
            $idColumn,
            $extra
        ) === 0;
    }

    /**
     * Get the excluded ID column and value for the unique rule.
     *
     * @param array $parameters
     * @return array
     */
    protected function getUniqueIds(array $parameters): array
    {
        $idColumn = $parameters[3] ?? 'id';

        return [$idColumn, $parameters[2]];
    }

    /**
     * Get the extra conditions for a unique rule.
     *
     * @param array $parameters
     * @return array
     */
    protected function getUniqueExtra(array $parameters): array
    {
        if (isset($parameters[4])) {
            return $this->getExtraConditions(array_slice($parameters, 4));
        } else {
            return [];
        }
    }

    /**
     * Validate the existence of an attribute value in a database table.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateExists(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'exists');

        $table = $parameters[0];

        // The second parameter position holds the name of the column that should be
        // verified as existing. If this parameter is not specified we will guess
        // that the columns being "verified" shares the given attribute's name.
        $column = $parameters[1] ?? $attribute;

        $expected = is_array($value) ? count($value) : 1;

        return $this->getExistCount($table, $column, $value, $parameters) >= $expected;
    }

    /**
     * Get the number of records that exist in storage.
     *
     * @param string $table
     * @param string $column
     * @param mixed $value
     * @param array $parameters
     * @return int
     */
    protected function getExistCount(string $table, string $column, mixed $value, array $parameters): int
    {
        $verifier = $this->getPresenceVerifier();

        $extra = $this->getExtraExistConditions($parameters);

        if (is_array($value)) {
            return $verifier->getMultiCount($table, $column, $value, $extra);
        } else {
            return $verifier->getCount($table, $column, $value, null, null, $extra);
        }
    }

    /**
     * Get the extra exist conditions.
     *
     * @param array $parameters
     * @return array
     */
    protected function getExtraExistConditions(array $parameters): array
    {
        return $this->getExtraConditions(array_values(array_slice($parameters, 2)));
    }

    /**
     * Get the extra conditions for a unique / exists rule.
     *
     * @param array $segments
     * @return array
     */
    protected function getExtraConditions(array $segments): array
    {
        $extra = [];

        $count = count($segments);

        for ($i = 0; $i < $count; $i = $i + 2) {
            $extra[$segments[$i]] = $segments[$i + 1];
        }

        return $extra;
    }

    /**
     * Validate that an attribute is a valid IP.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateIp(string $attribute, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate that an attribute is a valid IPv4.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateIpv4(string $attribute, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Validate that an attribute is a valid IPv6.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateIpv6(string $attribute, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Validate that an attribute is a valid e-mail address.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateEmail(string $attribute, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate that an attribute is a valid URL.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateUrl(string $attribute, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate that an attribute is an active URL.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateActiveUrl(string $attribute, mixed $value): bool
    {
        $url = str_replace(['http://', 'https://', 'ftp://'], '', strtolower($value));

        return checkdnsrr($url);
    }

    /**
     * Validate the MIME type of file is an image MIME type.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateImage(string $attribute, mixed $value): bool
    {
        return $this->validateMimes($attribute, $value, ['jpeg', 'png', 'gif', 'bmp']);
    }

    /**
     * Validate the MIME type of file upload attribute is in a set of MIME types.
     *
     * @param string $attribute
     * @param array $value
     * @param array $parameters
     * @return bool
     */
    protected function validateMimes(string $attribute, array $value, array $parameters): bool
    {
        if (! in_array($value, $_FILES, true)) {
            return false;
        }

        if (! empty($value['tmp_name']) && is_uploaded_file($value['tmp_name']) && $value['error']) {
            return false;
        }

        // The Symfony File class should do a decent job of guessing the extension
        // based on the true MIME type so we'll just loop through the array of
        // extensions and compare it to the guessed extension of the files.
        if ($value['tmp_name'] !== '') {
            return in_array(pathinfo($value['name'], PATHINFO_EXTENSION), $parameters, true);
        } else {
            return false;
        }
    }

    /**
     * Validate that an attribute contains only alphabetic characters.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool|int
     */
    protected function validateAlpha(string $attribute, mixed $value): bool|int
    {
        return preg_match('/^[\pL\pM]+$/u', $value);
    }

    /**
     * Validate that an attribute contains only alphanumeric characters.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool|int
     */
    protected function validateAlphaNum(string $attribute, mixed $value): bool|int
    {
        return preg_match('/^[\pL\pM\pN]+$/u', $value);
    }

    /**
     * Validate that an attribute contains only alphanumeric characters, dashes, and underscores.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool|int
     */
    protected function validateAlphaDash(string $attribute, mixed $value): bool|int
    {
        return preg_match('/^[\pL\pM\pN_-]+$/u', $value);
    }

    /**
     * Validate that an attribute passes a regular expression check.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool|int
     * @throws TypeException
     */
    protected function validateRegex(string $attribute, mixed $value, array $parameters): bool|int
    {
        $this->requireParameterCount(1, $parameters, 'regex');

        return preg_match($parameters[0], $value);
    }

    /**
     * Validate that an attribute is a valid date.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateDate(string $attribute, mixed $value): bool
    {
        if ($value instanceof DateTime) {
            return true;
        }

        if (strtotime($value) === false) {
            return false;
        }

        $date = date_parse($value);

        return checkdate((int) $date['month'], (int) $date['day'], (int) $date['year']);
    }

    /**
     * Validate that an attribute matches a date format.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateDateFormat(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'date_format');

        $parsed = date_parse_from_format($parameters[0], $value);

        return $parsed['error_count'] === 0 && $parsed['warning_count'] === 0;
    }

    /**
     * Validate the date is before a given date.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateBefore(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'before');

        if ($format = $this->getDateFormat($attribute)) {
            return $this->validateBeforeWithFormat($format, $value, $parameters);
        }

        if (! ($date = strtotime($parameters[0]))) {
            return strtotime($value) < strtotime((string) $this->getValue($parameters[0]));
        } else {
            return strtotime($value) < $date;
        }
    }

    /**
     * Validate the date is before a given date with a given format.
     *
     * @param string $format
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateBeforeWithFormat(string $format, mixed $value, array $parameters): bool
    {
        $param = $this->getValue($parameters[0]) ?: $parameters[0];

        return $this->checkDateTimeOrder($format, $value, $param);
    }

    /**
     * Validate the date is after a given date.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @throws TypeException
     */
    protected function validateAfter(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'after');

        if ($format = $this->getDateFormat($attribute)) {
            return $this->validateAfterWithFormat($format, $value, $parameters);
        }

        if (! ($date = strtotime($parameters[0]))) {
            return strtotime($value) > strtotime((string) $this->getValue($parameters[0]));
        } else {
            return strtotime($value) > $date;
        }
    }

    /**
     * Validate the date is after a given date with a given format.
     *
     * @param string $format
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateAfterWithFormat(string $format, mixed $value, array $parameters): bool
    {
        $param = $this->getValue($parameters[0]) ?: $parameters[0];

        return $this->checkDateTimeOrder($format, $param, $value);
    }

    /**
     * Given two date/time strings, check that one is after the other.
     *
     * @param string $format
     * @param string $before
     * @param string $after
     * @return bool
     */
    protected function checkDateTimeOrder(string $format, string $before, string $after): bool
    {
        $before = $this->getDateTimeWithOptionalFormat($format, $before);

        $after = $this->getDateTimeWithOptionalFormat($format, $after);

        return ($before && $after) && ($after > $before);
    }

    /**
     * Get a DateTime instance from a string.
     *
     * @param string $format
     * @param string $value
     * @return DateTime|null
     */
    protected function getDateTimeWithOptionalFormat(string $format, string $value): ?DateTime
    {
        $date = DateTime::createFromFormat($format, $value);

        if ($date) {
            return $date;
        }

        try {
            return new DateTime($value);
        } catch (Exception | \Exception $e) {
            return null;
        }
    }

    /**
     * Validate that an attribute is a valid timezone.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     * @throws \Exception
     */
    protected function validateTimezone(string $attribute, mixed $value): bool
    {
        try {
            new DateTimeZone($value);
        } catch (Exception | \Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Get the date format for an attribute if it has one.
     */
    protected function getDateFormat(string $attribute)
    {
        if ($result = $this->getRule($attribute, 'DateFormat')) {
            return $result[1][0];
        }
    }

    /**
     * Get the validation message for an attribute and rule.
     */
    protected function getMessage(string $attribute, string $rule)
    {
        $lowerRule = snake_case($rule);

        $inlineMessage = $this->getInlineMessage($attribute, $lowerRule);

        // First we will retrieve the custom message for the validation rule if one
        // exists. If a custom validation message is being used we'll return the
        // custom message, otherwise we'll keep searching for a valid message.
        if (null !== $inlineMessage) {
            return $inlineMessage;
        }

        $customKey = "validation.custom.{$attribute}.{$lowerRule}";

        $customMessage = $this->translator->trans($customKey);

        // First we check for a custom defined validation message for the attribute
        // and rule. This allows the developer to specify specific messages for
        // only some attributes and rules that need to get specially formed.
        if ($customMessage !== $customKey) {
            return $customMessage;
        } elseif (in_array($rule, $this->sizeRules, true)) {
            return $this->getSizeMessage($attribute, $rule);
        }

        // Finally, if no developer specified messages have been set, and no other
        // special messages apply for this rule, we will just pull the default
        // messages out of the translator service for this validation rule.
        $key = "validation.{$lowerRule}";

        if ($key !== ($value = $this->translator->trans($key))) {
            return $value;
        }

        return $this->getInlineMessage(
            $attribute,
            $lowerRule,
            $this->fallbackMessages
        ) ?: $key;
    }

    /**
     * Get the inline message for a rule if it exists.
     *
     * @param string $attribute
     * @param string $lowerRule
     * @param array|null $source
     * @return mixed|void
     */
    protected function getInlineMessage(string $attribute, string $lowerRule, ?array $source = null)
    {
        $source = $source ?: $this->customMessages;

        $keys = ["{$attribute}.{$lowerRule}", $lowerRule];

        // First we will check for a custom message for an attribute specific rule
        // message for the fields, then we will check for a general custom line
        // that is not attribute specific. If we find either we'll return it.
        foreach ($keys as $key) {
            if (isset($source[$key])) {
                return $source[$key];
            }
        }
    }

    /**
     * Get the proper error message for an attribute and size rule.
     */
    protected function getSizeMessage(string $attribute, string $rule): string
    {
        $lowerRule = snake_case($rule);

        // There are three different types of size validations. The attribute may be
        // either a number, file, or string so we will check a few things to know
        // which type of value it is and return the correct line for that type.
        $type = $this->getAttributeType($attribute);

        $key = "validation.{$lowerRule}.{$type}";

        return $this->translator->trans($key);
    }

    /**
     * Get the data type of the given attribute.
     */
    protected function getAttributeType(string $attribute): string
    {
        // We assume that the attributes present in the file array are files so that
        // means that if the attribute does not have a numeric rule and the files
        // list doesn't have it we'll just consider it a string by elimination.
        if ($this->hasRule($attribute, $this->numericRules)) {
            return 'numeric';
        } elseif ($this->hasRule($attribute, ['Array'])) {
            return 'array';
        } elseif (array_key_exists($attribute, $this->files)) {
            return 'file';
        }

        return 'string';
    }

    /**
     * Replace all error message place-holders with actual values.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function doReplacements(
        string $message,
        string $attribute,
        string $rule,
        array $parameters
    ): string {
        $value = $this->getAttribute($attribute);

        $message = str_replace(
            [':ATTRIBUTE', ':Attribute', ':attribute'],
            [strtoupper($value), ucfirst($value), $value],
            $message
        );

        if (isset($this->replacers[snake_case($rule)])) {
            $message = $this->callReplacer($message, $attribute, snake_case($rule), $parameters);
        } elseif (method_exists($this, $replacer = "replace{$rule}")) {
            $message = $this->$replacer($message, $attribute, $rule, $parameters);
        }

        return $message;
    }

    /**
     * Transform an array of attributes to their displayable form.
     *
     * @param array $values
     * @return array
     */
    protected function getAttributeList(array $values): array
    {
        $attributes = [];

        // For each attribute in the list we will simply get its displayable form as
        // this is convenient when replacing lists of parameters like some of the
        // replacement functions do when formatting out the validation message.
        foreach ($values as $key => $value) {
            $attributes[$key] = $this->getAttribute($value);
        }

        return $attributes;
    }

    /**
     * Get the displayable name of the attribute.
     */
    protected function getAttribute(string $attribute): string
    {
        // The developer may dynamically specify the array of custom attributes
        // on this Validator instance. If the attribute exists in this array
        // it takes precedence over all other ways we can pull attributes.
        if (isset($this->customAttributes[$attribute])) {
            return $this->customAttributes[$attribute];
        }

        $key = "validation.attributes.{$attribute}";

        // We allow for the developer to specify language lines for each of the
        // attributes allowing for more displayable counterparts of each of
        // the attributes. This provides the ability for simple formats.
        if (($line = $this->translator->trans($key)) !== $key) {
            return $line;
        } else {
            return str_replace('_', ' ', snake_case($attribute));
        }
    }

    /**
     * Get the displayable name of the value.
     *
     * @param string $attribute
     * @param mixed $value
     * @return mixed|string
     */
    public function getDisplayableValue(string $attribute, mixed $value): mixed
    {
        if (isset($this->customValues[$attribute][$value])) {
            return $this->customValues[$attribute][$value];
        }

        $key = "validation.values.{$attribute}.{$value}";

        if (($line = $this->translator->trans($key)) !== $key) {
            return $line;
        } else {
            return $value;
        }
    }

    /**
     * Replace all place-holders for the between rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceBetween(
        string $message,
        string $attribute,
        string $rule,
        array $parameters
    ): string {
        return str_replace([':min', ':max'], $parameters, $message);
    }

    /**
     * Replace all place-holders for the digits rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceDigits(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':digits', $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the digits (between) rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceDigitsBetween(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace([':min', ':max'], $parameters, $message);
    }

    /**
     * Replace all place-holders for the size rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceSize(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':size', $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the min rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceMin(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':min', $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the max rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceMax(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':max', $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the in rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceIn(string $message, string $attribute, string $rule, array $parameters): string
    {
        foreach ($parameters as &$parameter) {
            $parameter = $this->getDisplayableValue($attribute, $parameter);
        }

        return str_replace(':values', implode(', ', $parameters), $message);
    }

    /**
     * Replace all place-holders for the not_in rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceNotIn(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceIn($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the mimes rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceMimes(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':values', implode(', ', $parameters), $message);
    }

    /**
     * Replace all place-holders for the required_with rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceRequiredWith(string $message, string $attribute, string $rule, array $parameters): string
    {
        $parameters = $this->getAttributeList($parameters);

        return str_replace(':values', implode(' / ', $parameters), $message);
    }

    /**
     * Replace all place-holders for the required_with_all rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceRequiredWithAll(
        string $message,
        string $attribute,
        string $rule,
        array $parameters
    ): string {
        return $this->replaceRequiredWith($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the required_without rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceRequiredWithout(
        string $message,
        string $attribute,
        string $rule,
        array $parameters
    ): string {
        return $this->replaceRequiredWith($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the required_without_all rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceRequiredWithoutAll(
        string $message,
        string $attribute,
        string $rule,
        array $parameters
    ): string {
        return $this->replaceRequiredWith($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the required_if rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceRequiredIf(string $message, string $attribute, string $rule, array $parameters): string
    {
        $parameters[1] = $this->getDisplayableValue($parameters[0], return_array($this->data, $parameters[0]));

        $parameters[0] = $this->getAttribute($parameters[0]);

        return str_replace([':other', ':value'], $parameters, $message);
    }

    /**
     * Replace all place-holders for the same rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceSame(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':other', $this->getAttribute($parameters[0]), $message);
    }

    /**
     * Replace all place-holders for the different rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceDifferent(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceSame($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the date_format rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceDateFormat(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':format', $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the before rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceBefore(string $message, string $attribute, string $rule, array $parameters): string
    {
        if (! strtotime($parameters[0])) {
            return str_replace(':date', $this->getAttribute($parameters[0]), $message);
        } else {
            return str_replace(':date', $parameters[0], $message);
        }
    }

    /**
     * Replace all place-holders for the after rule.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replaceAfter(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceBefore($message, $attribute, $rule, $parameters);
    }

    /**
     * Determine if the given attribute has a rule in the given set.
     *
     * @param string $attribute
     * @param array|string $rules
     * @return bool
     */
    protected function hasRule(string $attribute, array|string $rules): bool
    {
        return !is_null__($this->getRule($attribute, $rules));
    }

    /**
     * Get a rule and its parameters for a given attribute.
     *
     * @param string $attribute
     * @param array|string $rules
     * @return array|null
     */
    protected function getRule(string $attribute, array|string $rules): ?array
    {
        if (! array_key_exists($attribute, $this->rules)) {
            return null;
        }

        $rules = (array) $rules;

        foreach ($this->rules[$attribute] as $rule) {
            [$rule, $parameters] = $this->parseRule($rule);

            if (in_array($rule, $rules, true)) {
                return [$rule, $parameters];
            }
        }

        return null;
    }

    /**
     * Extract the rule name and parameters from a rule.
     *
     * @param array|string $rules
     * @return array
     */
    protected function parseRule(array|string $rules): array
    {
        if (is_array($rules)) {
            return $this->parseArrayRule($rules);
        }

        return $this->parseStringRule($rules);
    }

    /**
     * Parse an array based rule.
     *
     * @param array $rules
     * @return array
     */
    protected function parseArrayRule(array $rules): array
    {
        return [studly_case(trim((string) return_array($rules, "0"))), array_slice($rules, 1)];
    }

    /**
     * Parse a string based rule.
     *
     * @param string $rules
     * @return array
     */
    protected function parseStringRule(string $rules): array
    {
        $parameters = [];

        // The format for specifying validation rules and parameters follows an
        // easy {rule}:{parameters} formatting convention. For instance the
        // rule "Max:3" states that the value may only be three letters.
        if (!is_false__(strpos($rules, ':'))) {
            [$rules, $parameter] = explode(':', $rules, 2);

            $parameters = $this->parseParameters($rules, $parameter);
        }

        return [studly_case(trim($rules)), $parameters];
    }

    /**
     * Parse a parameter list.
     *
     * @param string $rule
     * @param string $parameter
     * @return array
     */
    protected function parseParameters(string $rule, string $parameter): array
    {
        if (strtolower($rule) === 'regex') {
            return [$parameter];
        }

        return str_getcsv($parameter);
    }

    /**
     * Get the array of custom validator extensions.
     *
     * @return array
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Register an array of custom validator extensions.
     *
     * @param array $extensions
     */
    public function addExtensions(array $extensions): void
    {
        if ($extensions) {
            $keys = array_map('\Qubus\Support\Helpers\snake_case', array_keys($extensions));

            $extensions = array_combine($keys, array_values($extensions));
        }

        $this->extensions = array_merge($this->extensions, $extensions);
    }

    /**
     * Register an array of custom implicit validator extensions.
     *
     * @param array $extensions
     */
    public function addImplicitExtensions(array $extensions): void
    {
        $this->addExtensions($extensions);

        foreach ($extensions as $rule => $extension) {
            $this->implicitRules[] = studly_case($rule);
        }
    }

    /**
     * Register a custom validator extension.
     *
     * @param string $rule
     * @param string|Closure $extension
     */
    public function addExtension(string $rule, string|Closure $extension): void
    {
        $this->extensions[snake_case($rule)] = $extension;
    }

    /**
     * Register a custom implicit validator extension.
     *
     * @param string $rule
     * @param string|Closure $extension
     */
    public function addImplicitExtension(string $rule, string|Closure $extension): void
    {
        $this->addExtension($rule, $extension);

        $this->implicitRules[] = studly_case($rule);
    }

    /**
     * Get the array of custom validator message replacers.
     *
     * @return array
     */
    public function getReplacers(): array
    {
        return $this->replacers;
    }

    /**
     * Register an array of custom validator message replacers.
     *
     * @param array $replacers
     */
    public function addReplacers(array $replacers): void
    {
        if ($replacers) {
            $keys = array_map('\Qubus\Support\Helpers\snake_case', array_keys($replacers));

            $replacers = array_combine($keys, array_values($replacers));
        }

        $this->replacers = array_merge($this->replacers, $replacers);
    }

    /**
     * Register a custom validator message replacer.
     *
     * @param string $rule
     * @param string|Closure $replacer
     */
    public function addReplacer(string $rule, string|Closure $replacer): void
    {
        $this->replacers[snake_case($rule)] = $replacer;
    }

    /**
     * Get the data under validation.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set the data under validation.
     *
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $this->parseData($data);
    }

    /**
     * Get the validation rules.
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Set the validation rules.
     *
     * @param array $rules
     * @return $this
     */
    public function setRules(array $rules): static
    {
        $this->rules = $this->explodeRules($rules);

        return $this;
    }

    /**
     * Set the custom attributes on the validator.
     *
     * @param array $attributes
     * @return $this
     */
    public function setAttributeNames(array $attributes): static
    {
        $this->customAttributes = $attributes;

        return $this;
    }

    /**
     * Set the custom values on the validator.
     *
     * @param array $values
     * @return $this
     */
    public function setValueNames(array $values): static
    {
        $this->customValues = $values;

        return $this;
    }

    /**
     * Get the files under validation.
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Set the files under validation.
     *
     * @param array $files
     * @return $this
     */
    public function setFiles(array $files): static
    {
        $this->files = $files;

        return $this;
    }

    /**
     * Get the Presence Verifier implementation.
     *
     * @return PresenceVerifier|null
     */
    public function getPresenceVerifier(): ?PresenceVerifier
    {
        if (! isset($this->presenceVerifier)) {
            throw new RuntimeException('Presence verifier has not been set.');
        }

        return $this->presenceVerifier;
    }

    /**
     * Set the Presence Verifier implementation.
     */
    public function setPresenceVerifier(PresenceVerifier $presenceVerifier): void
    {
        $this->presenceVerifier = $presenceVerifier;
    }

    /**
     * Get the StringTranslator implementation.
     *
     * @return StringTranslator
     */
    public function getTranslator(): StringTranslator
    {
        return $this->translator;
    }

    /**
     * Set the StringTranslator implementation.
     */
    public function setTranslator(StringTranslator $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * Get the custom messages for the validator.
     *
     * @return array
     */
    public function getCustomMessages(): array
    {
        return $this->customMessages;
    }

    /**
     * Set the custom messages for the validator.
     *
     * @param array $messages
     */
    public function setCustomMessages(array $messages): void
    {
        $this->customMessages = array_merge($this->customMessages, $messages);
    }

    /**
     * Get the custom attributes used by the validator.
     *
     * @return array
     */
    public function getCustomAttributes(): array
    {
        return $this->customAttributes;
    }

    /**
     * Add custom attributes to the validator.
     *
     * @param array $customAttributes
     * @return $this
     */
    public function addCustomAttributes(array $customAttributes): static
    {
        $this->customAttributes = array_merge($this->customAttributes, $customAttributes);

        return $this;
    }

    /**
     * Get the custom values for the validator.
     *
     * @return array
     */
    public function getCustomValues(): array
    {
        return $this->customValues;
    }

    /**
     * Add the custom values for the validator.
     *
     * @param array $customValues
     * @return $this
     */
    public function addCustomValues(array $customValues): static
    {
        $this->customValues = array_merge($this->customValues, $customValues);

        return $this;
    }

    /**
     * Get the fallback messages for the validator.
     *
     * @return array
     */
    public function getFallbackMessages(): array
    {
        return $this->fallbackMessages;
    }

    /**
     * Set the fallback messages for the validator.
     *
     * @param array $messages
     */
    public function setFallbackMessages(array $messages): void
    {
        $this->fallbackMessages = $messages;
    }

    /**
     * Get the failed validation rules.
     *
     * @return array
     */
    public function failed(): array
    {
        if (! $this->messages) {
            $this->passes();
        }

        return $this->failedRules;
    }

    /**
     * Get the message container for the validator.
     */
    public function messages(): MessageBag
    {
        if (! $this->messages) {
            $this->passes();
        }

        return $this->messages;
    }

    /**
     * An alternative more semantic shortcut to the message container.
     */
    public function errors(): MessageBag
    {
        return $this->messages();
    }

    /**
     * Call a custom validator extension.
     *
     * @param string $rule
     * @param array $parameters
     * @return mixed
     */
    protected function callExtension(string $rule, array $parameters): mixed
    {
        $callback = $this->extensions[$rule];

        if ($callback instanceof Closure) {
            return call_user_func_array($callback, $parameters);
        } elseif (is_string($callback)) {
            return $this->callClassBasedExtension($callback, $parameters);
        }

        return false;
    }

    /**
     * Call a class based validator extension.
     *
     * @param string $callback
     * @param array $parameters
     * @return bool
     */
    protected function callClassBasedExtension(string $callback, array $parameters): bool
    {
        [$class, $method] = explode('@', $callback);

        return call_user_func_array([$class, $method], $parameters);
    }

    /**
     * Call a custom validator message replacer.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return mixed
     */
    protected function callReplacer(string $message, string $attribute, string $rule, array $parameters): mixed
    {
        $callback = $this->replacers[$rule];

        if ($callback instanceof Closure) {
            return call_user_func_array($callback, func_get_args());
        } elseif (is_string($callback)) {
            return $this->callClassBasedReplacer($callback, $message, $attribute, $rule, $parameters);
        }
    }

    /**
     * Call a class based validator message replacer.
     *
     * @param string $callback
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function callClassBasedReplacer(
        string $callback,
        string $message,
        string $attribute,
        string $rule,
        array $parameters
    ): string {
        [$class, $method] = explode('@', $callback);

        return call_user_func_array([$class, $method], array_slice(func_get_args(), 1));
    }

    /**
     * Require a certain number of parameters to be present.
     *
     * @param int $count
     * @param array $parameters
     * @param string $rule
     * @throws TypeException
     */
    protected function requireParameterCount(int $count, array $parameters, string $rule): void
    {
        if (count($parameters) < $count) {
            throw new TypeException("Validation rule $rule requires at least $count parameters.");
        }
    }

    /**
     * Handle dynamic calls to class methods.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $parameters)
    {
        $rule = snake_case(substr($method, 8));

        if (isset($this->extensions[$rule])) {
            return $this->callExtension($rule, $parameters);
        }

        throw new BadMethodCallException(sprintf('Method [%s] does not exist.', $method));
    }
}
