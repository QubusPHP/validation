<?php

declare(strict_types=1);

namespace Qubus\Validation;

use Psr\Http\Message\UriInterface;
use Qubus\Exception\Exception;
use Qubus\Exception\Http\HttpException;
use Throwable;

use function sprintf;

class ValidationException extends Exception implements HttpException
{
    public function __construct(
        protected UriInterface|string|null $uri = null,
        string $message = '',
        ?Throwable $previous = null,
        protected array $headers = [],
        protected $code = 0,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return static::class . sprintf(
            ' %s in %s(%s)',
            $this->message,
            $this->file,
            $this->line,
        ) . "\n" . sprintf('%s', $this->getTraceAsString());
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return (int) $this->code;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): UriInterface|string|null
    {
        return $this->uri;
    }
}
