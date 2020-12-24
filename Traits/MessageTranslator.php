<?php

/**
 * Qubus\Validation
 *
 * @link       https://github.com/QubusPHP/validation
 * @copyright  2020 Joshua Parker
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 *
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Qubus\Validation\Traits;

use function array_key_exists;
use function array_merge;
use function explode;
use function is_array;
use function Qubus\Support\Helpers\value;

trait MessageTranslator
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
     * @return string
     */
    public function trans(string $key)
    {
        return $this->arrayGet($this->messages, $key, $key);
    }

    /**
     * Use dot(.) string.
     *
     * @param array  $array
     * @param mixed  $default
     * @return mixed
     */
    protected function arrayGet(array $array, ?string $key, $default = null)
    {
        if (null === $key) {
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
