<?php

/**
 * Author: Marek Baron
 * GitHub: https://www.github.com/marek-baron
 * Project: marek-baron/dependency-injector
 */

declare(strict_types=1);

namespace MarekBaron\Container;

use Psr\Container\ContainerInterface;

interface ResolverInterface
{
    public function resolve(ContainerInterface $container, string $id): mixed;
}
