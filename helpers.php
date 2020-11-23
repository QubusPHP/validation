<?php

declare(strict_types=1);

namespace Qubus\Validation;

use ArrayAccess;
use Closure;

if (!function_exists('Qubus\Validation\array_get')) {
    /**
     * Return array specific item.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    function array_get(array $array, ?string $key, $default = null)
    {
        if (!array_accessible($array)) {
            return value($default);
        }

        if (null === $key) {
            return $array;
        }

        if (array_exists($array, $key)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (array_accessible($array) && array_exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }
}

if (!function_exists('Qubus\Validation\array_dot')) {
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    function array_dot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, array_dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }
}

if (!function_exists('Qubus\Validation\array_accessible')) {
    /**
     * Check input is array accessable.
     *
     * @param mixed $value
     * @return bool
     */
    function array_accessible($value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }
}

if (!function_exists('Qubus\Validation\array_exists')) {
    /**
     * Check array key exists.
     *
     * @param array  $array
     * @param string $key
     * @return bool
     */
    function array_exists(array $array, string $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }
}

if (!function_exists('Qubus\Validation\snake_case')) {
    /**
     * Convert a string to snake case.
     *
     * @param string $string
     * @param string $delimiter
     * @return string
     */
    function snake_case(string $string, string $delimiter = '_'): string
    {
        $replace = '$1'.$delimiter.'$2';

        return ctype_lower($string) ? $string : strtolower(preg_replace('/(.)([A-Z])/', $replace, $string));
    }
}

if (!function_exists('Qubus\Validation\studly_case')) {
    /**
     * Convert a value to studly caps case.
     *
     * @param string $string
     * @return string
     */
    function studly_case(string $string): string
    {
        $string = ucwords(str_replace(['-', '_'], ' ', $string));

        return str_replace(' ', '', $string);
    }
}

if (!function_exists('Qubus\Validation\value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
