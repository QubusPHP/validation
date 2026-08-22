<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules\Traits;

use InvalidArgumentException;

trait SizeAware
{
    /**
     * Get size (int) value from given $value
     *
     * @param mixed $value
     * @return float|false
     */
    protected function getValueSize(mixed $value): float|false
    {
        if (
            $this->getAttribute()
            && (
                $this->getAttribute()->hasRule('numeric')
                || $this->getAttribute()->hasRule('integer')
                || $this->getAttribute()->hasRule('int')
            )
            && is_numeric($value)
        ) {
            $value = (float) $value;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        } elseif (is_string($value)) {
            return (float) mb_strlen($value, 'UTF-8');
        } elseif ($this->isUploadedFileValue($value)) {
            return (float) $value['size'];
        } elseif (is_array($value)) {
            return (float) count($value);
        } else {
            return false;
        }
    }

    /**
     * Given $size and get the bytes
     *
     * @param mixed $size
     * @return float
     * @throws InvalidArgumentException
     */
    protected function getBytesSize(mixed $size): float
    {
        if (is_numeric($size)) {
            return (float) $size;
        }

        if (!is_string($size)) {
            throw new InvalidArgumentException("Size must be string or numeric Bytes", 1);
        }

        if (!preg_match("/^(?<number>((\d+)?\.)?\d+)(?<format>(B|K|M|G|T|P)B?)?$/i", $size, $match)) {
            throw new InvalidArgumentException("Size is not valid format", 1);
        }

        $number = (float) $match['number'];
        $format = $match['format'] ?? '';

        return match (strtoupper($format)) {
            "KB", "K" => $number * 1024,
            "MB", "M" => $number * pow(1024, 2),
            "GB", "G" => $number * pow(1024, 3),
            "TB", "T" => $number * pow(1024, 4),
            "PB", "P" => $number * pow(1024, 5),
            default => $number,
        };
    }

    /**
     * Check whether value is from $_FILES
     *
     * @param mixed $value
     * @return bool
     */
    public function isUploadedFileValue(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $keys = ['name', 'type', 'tmp_name', 'size', 'error'];
        return array_all($keys, fn($key) => array_key_exists($key, $value));
    }
}
