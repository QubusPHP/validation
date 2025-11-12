<?php

declare(strict_types=1);

namespace Qubus\Validation;

use Psr\Http\Message\UriInterface;
use Qubus\Exception\Exception;
use Qubus\Exception\Http\HttpException;
use Throwable;

use function sprintf;

class ValidationException extends HttpException
{
    public function __construct(
        protected UriInterface|string|null $uri = null,
        string $message = '',
        ?Throwable $previous = null,
        protected array $headers = [],
        protected $code = 0,
    ) {
        parent::__construct($uri, $message, $previous, $headers, $code);
    }
}
