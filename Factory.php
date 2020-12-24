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

namespace Qubus\Validation;

use Closure;
use Qubus\Validation\Interfaces\PresenceVerifier;
use Qubus\Validation\Translators\DefaultTranslator;
use Qubus\Validation\Translators\StringTranslator;
use Qubus\Validation\Validator;

use function call_user_func;
use function Qubus\Support\Helpers\snake_case;

class Factory
{
    /**
     * The StringTranslator implementation.
     */
    protected StringTranslator $translator;

    /**
     * The Presence Verifier implementation.
     */
    protected ?PresenceVerifier $verifier = null;

    /**
     * All of the custom validator extensions.
     *
     * @var array $extensions
     */
    protected array $extensions = [];

    /**
     * All of the custom implicit validator extensions.
     *
     * @var array $implicitExtensions
     */
    protected array $implicitExtensions = [];

    /**
     * All of the custom validator message replacers.
     *
     * @var array $replacers
     */
    protected array $replacers = [];

    /**
     * All of the fallback messages for custom rules.
     *
     * @var array $fallbackMessages
     */
    protected array $fallbackMessages = [];

    /**
     * The Validator resolver instance.
     */
    protected ?Closure $resolver = null;

    /**
     * Create a new Validator factory instance.
     *
     * @return Factory
     */
    public function __construct(?StringTranslator $translator = null)
    {
        $this->translator = $translator ?: new DefaultTranslator();
    }

    /**
     * Create a new Validator instance.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return Validator
     */
    public function make(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        // The presence verifier is responsible for checking the unique and exists data
        // for the validator. It is behind an interface so that multiple versions of
        // it may be written besides database. We'll inject it into the validator.
        $validator = $this->resolve($data, $rules, $messages, $customAttributes);

        if (null !== $this->verifier) {
            $validator->setPresenceVerifier($this->verifier);
        }

        $this->addExtensions($validator);

        return $validator;
    }

    /**
     * Add the extensions to a validator instance.
     */
    protected function addExtensions(Validator $validator)
    {
        $validator->addExtensions($this->extensions);

        // Next, we will add the implicit extensions, which are similar to the required
        // and accepted rule in that they are run even if the attributes is not in a
        // array of data that is given to a validator instances via instantiation.
        $implicit = $this->implicitExtensions;

        $validator->addImplicitExtensions($implicit);

        $validator->addReplacers($this->replacers);

        $validator->setFallbackMessages($this->fallbackMessages);
    }

    /**
     * Resolve a new Validator instance.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return Validator
     */
    protected function resolve(array $data, array $rules, array $messages, array $customAttributes)
    {
        if (null === $this->resolver) {
            return new Validator($this->translator, $data, $rules, $messages, $customAttributes);
        } else {
            return call_user_func($this->resolver, $this->translator, $data, $rules, $messages, $customAttributes);
        }
    }

    /**
     * Register a custom validator extension.
     *
     * @param string          $rule
     * @param Closure|string $extension
     * @param string          $message
     */
    public function extend($rule, $extension, $message = null): void
    {
        $this->extensions[$rule] = $extension;

        if ($message) {
            $this->fallbackMessages[snake_case($rule)] = $message;
        }
    }

    /**
     * Register a custom implicit validator extension.
     *
     * @param Closure|string $extension
     */
    public function extendImplicit(string $rule, $extension, ?string $message = null): void
    {
        $this->implicitExtensions[$rule] = $extension;

        if ($message) {
            $this->fallbackMessages[snake_case($rule)] = $message;
        }
    }

    /**
     * Register a custom implicit validator message replacer.
     *
     * @param Closure|string $replacer
     */
    public function replacer(string $rule, $replacer): void
    {
        $this->replacers[$rule] = $replacer;
    }

    /**
     * Set the Validator instance resolver.
     */
    public function resolver(Closure $resolver): void
    {
        $this->resolver = $resolver;
    }

    /**
     * Get the StringTranslator implementation.
     */
    public function getTranslator(): StringTranslator
    {
        return $this->translator;
    }

    /**
     * Get the Presence Verifier implementation.
     */
    public function getPresenceVerifier(): PresenceVerifier
    {
        return $this->verifier;
    }

    /**
     * Set the Presence Verifier implementation.
     */
    public function setPresenceVerifier(PresenceVerifier $presenceVerifier): void
    {
        $this->verifier = $presenceVerifier;
    }
}
