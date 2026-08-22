<?php

declare(strict_types=1);

namespace Qubus\Validation;

use Psr\Http\Message\UriInterface;
use Qubus\Exception\Http\HttpException;
use Throwable;

class ValidationException extends HttpException
{
    public function __construct(
        protected UriInterface|string|null $uri = null,
        string $message = '',
        protected $code = 400,
        ?Throwable $previous = null,
    ) {
        parent::__construct($uri, $message, $code, $previous);
    }
}
