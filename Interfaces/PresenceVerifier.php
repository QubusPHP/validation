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

namespace Qubus\Validation\Interfaces;

interface PresenceVerifier
{
    /**
     * Count the number of objects in a collection having the given value.
     *
     * @param string $collection
     * @param string $column
     * @param string $value
     * @param int|null $excludeId
     * @param string|null $idColumn
     * @param array $extra
     * @return int
     */
    public function getCount(
        string $collection,
        string $column,
        string $value,
        ?int $excludeId = null,
        ?string $idColumn = null,
        array $extra = []
    ): int;

    /**
     * Count the number of objects in a collection with the given values.
     *
     * @param string $collection
     * @param string $column
     * @param array $values
     * @param array $extra
     * @return int
     */
    public function getMultiCount(
        string $collection,
        string $column,
        array $values,
        array $extra = []
    ): int;
}
