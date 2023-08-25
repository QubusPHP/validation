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

namespace Qubus\Validation\Translators;

interface StringTranslator
{
    /**
     * Translator.
     *
     * @param string $key message key.
     * @return mixed
     */
    public function trans(string $key): mixed;
}
