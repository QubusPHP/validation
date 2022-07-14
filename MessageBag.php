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

namespace Qubus\Validation;

use Countable;
use JsonSerializable;

use function array_key_exists;
use function array_merge;
use function array_merge_recursive;
use function count;
use function in_array;
use function json_encode;
use function str_replace;

use const COUNT_RECURSIVE;

class MessageBag implements Countable, JsonSerializable
{
    /**
     * All of the registered messages.
     *
     * @var array $messages
     */
    protected array $messages = [];

    /**
     * Default format for message output.
     */
    protected string $format = ':message';

    /**
     * Create a new message bag instance.
     *
     * @param array $messages
     * @return MessageBag
     */
    public function __construct(array $messages = [])
    {
        foreach ($messages as $key => $value) {
            $this->messages[$key] = (array) $value;
        }
    }

    /**
     * Add a message to the bag.
     *
     * @return self
     */
    public function add(string $key, string $message): self
    {
        if ($this->isUnique($key, $message)) {
            $this->messages[$key][] = $message;
        }

        return $this;
    }

    /**
     * Merge a new array of messages into the bag.
     *
     * @param array $messages
     * @return self
     */
    public function merge(array $messages): self
    {
        $this->messages = array_merge_recursive($this->messages, $messages);

        return $this;
    }

    /**
     * Determine if a key and message combination already exists.
     */
    protected function isUnique(string $key, string $message): bool
    {
        $messages = (array) $this->messages;

        return ! isset($messages[$key]) || ! in_array($message, $messages[$key], true);
    }

    /**
     * Determine if messages exist for a given key.
     */
    public function has(?string $key = null): bool
    {
        return $this->first($key) !== '';
    }

    /**
     * Get the first message from the bag for a given key.
     */
    public function first(?string $key = null, ?string $format = null): string
    {
        $messages = null === $key ? $this->all($format) : $this->get($key, $format);

        return count($messages) > 0 ? $messages[0] : '';
    }

    /**
     * Get all of the messages from the bag for a given key.
     *
     * @return array
     */
    public function get(?string $key = null, ?string $format = null): array
    {
        $format = $this->checkFormat($format);

        // If the message exists in the container, we will transform it and return
        // the message. Otherwise, we'll return an empty array since the entire
        // methods is to return back an array of messages in the first place.
        if (array_key_exists($key, $this->messages)) {
            return $this->transform($this->messages[$key], $format, $key);
        }

        return [];
    }

    /**
     * Get all of the messages for every key in the bag.
     *
     * @return array
     */
    public function all(?string $format = null): array
    {
        $format = $this->checkFormat($format);

        $all = [];

        foreach ($this->messages as $key => $messages) {
            $all = array_merge($all, $this->transform($messages, $format, $key));
        }

        return $all;
    }

    /**
     * Format an array of messages.
     *
     * @param array  $messages
     * @return array
     */
    protected function transform(array $messages, string $format, string $messageKey): array
    {
        $messages = (array) $messages;

        // We will simply spin through the given messages and transform each one
        // replacing the :message place holder with the real message allowing
        // the messages to be easily formatted to each developer's desires.
        foreach ($messages as $key => &$message) {
            $replace = [':message', ':key'];

            $message = str_replace($replace, [$message, $messageKey], $format);
        }

        return $messages;
    }

    /**
     * Get the appropriate format based on the given format.
     */
    protected function checkFormat(?string $format): ?string
    {
        return $format ?? $this->format;
    }

    /**
     * Get the raw messages in the container.
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Get the messages for the instance.
     */
    public function getMessageBag(): MessageBag
    {
        return $this;
    }

    /**
     * Get the default message format.
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Set the default message format.
     *
     * @param string $format
     */
    public function setFormat($format = ':message'): MessageBag
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Determine if the message bag has any messages.
     */
    public function isEmpty(): bool
    {
        return ! $this->any();
    }

    /**
     * Determine if the message bag has any messages.
     */
    public function any(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Get the number of messages in the container.
     */
    public function count(): int
    {
        return count($this->messages, COUNT_RECURSIVE) - count($this->messages);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->getMessages();
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the message bag to its string representation.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
