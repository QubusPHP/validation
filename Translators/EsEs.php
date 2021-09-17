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

use Qubus\Validation\Traits\MessageTranslator;
use Qubus\Validation\Translators\StringTranslator;

class EsEs implements StringTranslator
{
    use MessageTranslator;

    /** @var array $defaultMessages */
    protected array $defaultMessages = [
        'accepted'             => 'Se debe aceptar el :attribute.',
        'active_url'           => 'El :attribute no es una URL válida.',
        'after'                => 'El :attribute debe ser una fecha posterior a :date.',
        'alpha'                => 'El :attribute solo puede contener letras.',
        'alpha_dash'           => 'El :attribute solo puede contener letras, números y guiones.',
        'alpha_num'            => 'El :attribute solo puede contener letras y números.',
        'array'                => 'El :attribute debe ser una matriz.',
        'before'               => 'El :attribute debe ser una fecha anterior a la :date.',
        'between'              => [
            'numeric' => 'El :attribute debe estar entre :min y :max.',
            'file'    => 'El :attribute debe estar entre :min y :max kilobytes.',
            'string'  => 'El :attribute debe estar entre :min y :max caracteres.',
            'array'   => 'El :attribute debe tener entre :min y :max elementos.',
        ],
        'boolean'              => 'El campo de :attribute debe ser verdadero o falso.',
        'confirmed'            => 'La confirmación del :attribute no coincide.',
        'date'                 => 'El :attribute no es una fecha válida.',
        'date_format'          => 'El :attribute no coincide con el formato :format.',
        'different'            => 'El :attribute y :other deben ser diferentes.',
        'digits'               => 'El :attribute debe ser :digits dígitos.',
        'digits_between'       => 'El :attribute debe estar entre :min y :max dígitos.',
        'email'                => 'El :attribute debe ser una dirección de correo electrónico válida.',
        'filled'               => 'El campo de :attribute es obligatorio.',
        'exists'               => 'El :attribute seleccionado no es válido.',
        'image'                => 'The :attribute must be an image.',
        'in'                   => 'El :attribute seleccionado no es válido.',
        'integer'              => 'El :attribute debe ser un número entero.',
        'ip'                   => 'El :attribute debe ser una dirección IP válida.',
        'ip4'                  => 'El :attribute no es una dirección IPv4 válida.',
        'ip6'                  => 'El :attribute no es una dirección IPv6 válida.',
        'max'                  => [
            'numeric' => 'El :attribute no puede ser mayor que :max.',
            'file'    => 'El :attribute no puede ser mayor que kilobytes :max.',
            'string'  => 'El :attribute no puede ser mayor que :max de caracteres.',
            'array'   => 'El :attribute no puede tener más de elementos :max.',
        ],
        'mimes'                => 'El :attribute debe ser un archivo de tipo: :values.',
        'min'                  => [
            'numeric' => 'El :attribute debe ser al menos :min.',
            'file'    => 'El :attribute debe tener al menos :min kilobytes.',
            'string'  => 'El :attribute debe tener al menos :min caracteres.',
            'array'   => 'El :attribute debe tener al menos elementos :min.',
        ],
        'not_in'               => 'El :attribute seleccionado no es válido.',
        'numeric'              => 'El :attribute debe ser un número.',
        'regex'                => 'El formato de :attribute no es válido.',
        'required'             => 'El campo de :attribute es obligatorio.',
        'required_if'          => 'El campo :attribute es obligatorio cuando :other es :value.',
        'required_with'        => 'El campo :attribute es obligatorio cuando :values está presente.',
        'required_with_all'    => 'El campo :attribute es obligatorio cuando :values está presente.',
        'required_without'     => 'El campo :attribute es obligatorio cuando :values no están presentes.',
        'required_without_all' => 'El campo de :attribute es obligatorio cuando no hay ninguno de :values presentes.',
        'same'                 => 'El :attribute y :other debe coincidir.',
        'size'                 => [
            'numeric' => 'El :attribute debe ser :size.',
            'file'    => 'El :attribute debe ser :size kilobytes.',
            'string'  => 'El :attribute debe ser caracteres de :size.',
            'array'   => 'El :attribute debe contener artículos de :size.',
        ],
        'unique'               => 'El :attribute ya se ha tomado.',
        'url'                  => 'El formato de :attribute no es válido.',
        'timezone'             => 'El :attribute debe ser una zona válida.',
    ];
}
