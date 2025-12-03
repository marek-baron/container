<?php

/**
 * Author: Marek Baron
 * GitHub: https://www.github.com/marek-baron
 * Project: marek-baron/container
 */

declare(strict_types=1);

namespace MarekBaron\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
    public function __construct(string $string, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($string, $code, $previous);
    }
}
