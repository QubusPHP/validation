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

namespace Qubus\Validation\Traits;

use function array_key_exists;
use function array_merge;
use function explode;
use function is_array;
use function Qubus\Support\Helpers\is_null__;
use function Qubus\Support\Helpers\value;

trait MessageTranslatorAware
{
    /** @var array $messages */
    protected array $messages = [];

    public function __construct(array $messages = [])
    {
        $this->messages = ['validation' => array_merge($this->defaultMessages, $messages)];
    }

    /**
     * Translate a string.
     *
     * @param string $key Key to be spliced.
     * @return mixed
     */
    public function trans(string $key): mixed
    {
        return $this->arrayGet($this->messages, $key, $key);
    }

    /**
     * Use dot(.) string.
     *
     * @param array $array
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    protected function arrayGet(array $array, ?string $key, mixed $default = null): mixed
    {
        if (is_null__($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }
}
