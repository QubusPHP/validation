<?php

declare(strict_types=1);

namespace Qubus\Validation;

use function implode;
use function Qubus\Support\Helpers\is_null__;
use function Qubus\Support\Helpers\snake_case;

class Helper
{
    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string $pattern
     * @param  string $value
     * @return bool
     */
    public static function strIs(string $pattern, string $value): bool
    {
        if ($pattern === $value) {
            return true;
        }

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern);

        return (bool) preg_match('#^' . $pattern . '\z#u', $value);
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param  array $array
     * @param  string $key
     * @return bool
     */
    public static function arrayHas(array $array, string $key): bool
    {
        if (array_key_exists($key, $array)) {
            return true;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array $array
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function arrayGet(array $array, ?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array  $array
     * @param  string $prepend
     * @return array
     */
    public static function arrayDot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::arrayDot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Set an item on an array or object using dot notation.
     *
     * @param mixed             $target
     * @param array|string|null $key
     * @param mixed             $value
     * @param bool              $overwrite
     * @return array
     */
    public static function arraySet(mixed &$target, array|string|null $key, mixed $value, bool $overwrite = true): array
    {
        if (is_null__($key)) {
            if ($overwrite) {
                return $target = array_merge($target, $value);
            }
            return $target = array_merge($value, $target);
        }

        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (! is_array($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    static::arraySet($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (is_array($target)) {
            if ($segments) {
                if (! array_key_exists($segment, $target)) {
                    $target[$segment] = [];
                }

                static::arraySet($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || ! array_key_exists($segment, $target)) {
                $target[$segment] = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                static::arraySet($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }

    /**
     * Unset an item on an array or object using dot notation.
     *
     * @param  mixed        $target
     * @param array|string $key
     * @return mixed
     */
    public static function arrayUnset(mixed &$target, array|string $key): mixed
    {
        if (!is_array($target)) {
            return $target;
        }

        $segments = is_array($key) ? $key : explode('.', $key);
        $segment = array_shift($segments);

        if ($segment == '*') {
            if ($segments) {
                foreach ($target as &$inner) {
                    static::arrayUnset($inner, $segments);
                }
                unset($inner);
            } else {
                $target = [];
            }
        } elseif ($segments) {
            if (array_key_exists($segment, $target)) {
                static::arrayUnset($target[$segment], $segments);
            }
        } elseif (array_key_exists($segment, $target)) {
            unset($target[$segment]);
        }

        return $target;
    }

    /**
     * Get snake_case format from given string
     *
     * @param  string $value
     * @param  string $delimiter
     * @return string
     */
    public static function snakeCase(string $value, string $delimiter = '_'): string
    {
        return snake_case($value, $delimiter);
    }

    /**
     * Join string[] to string with given $separator and $lastSeparator.
     *
     * @param array $pieces
     * @param string $separator
     * @param string|null $lastSeparator
     * @return string|int
     */
    public static function join(array $pieces, string $separator, ?string $lastSeparator = null): string|int
    {
        if (is_null__($lastSeparator)) {
            $lastSeparator = $separator;
        }

        $last = array_pop($pieces);

        return match (count($pieces)) {
            0 => is_string($last) || is_int($last) ? $last : (string) $last,
            1 => $pieces[0] . $lastSeparator . $last,
            default => implode($separator, $pieces) . $lastSeparator . $last,
        };
    }

    /**
     * Wrap string[] by given $prefix and $suffix
     *
     * @param  array        $strings
     * @param  string       $prefix
     * @param  string|null  $suffix
     * @return array
     */
    public static function wraps(array $strings, string $prefix, ?string $suffix = null): array
    {
        if (is_null__($suffix)) {
            $suffix = $prefix;
        }

        return array_map(function ($str) use ($prefix, $suffix) {
            return $prefix . $str . $suffix;
        }, $strings);
    }
}
