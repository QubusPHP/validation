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

namespace Qubus\Validation\Translators;

interface StringTranslator
{
    /**
     * Translator.
     *
     * @param string $key message key.
     * @return string
     */
    public function trans(string $key);
}
