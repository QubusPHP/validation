<?php

/**
 * Qubus\Validation
 *
 * @link       https://github.com/QubusPHP/validation
 * @copyright  2020 Joshua Parker <josh@joshuaparker.blog>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 *
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Qubus\Validation\Interfaces;

interface PresenceVerifier
{
    /**
     * Count the number of objects in a collection having the given value.
     *
     * @param array  $extra
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
     * @param array  $values
     * @param array  $extra
     */
    public function getMultiCount(
        string $collection,
        string $column,
        array $values,
        array $extra = []
    ): int;
}
