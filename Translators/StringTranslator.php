<?php

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
