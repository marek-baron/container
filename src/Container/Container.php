<?php

/**
 * Author: Marek Baron
 * GitHub: https://www.github.com/marek-baron
 * Project: marek-baron/container
 */

declare(strict_types=1);

namespace MarekBaron\Container;

use MarekBaron\Container\Exception\ContainerException;
use MarekBaron\Container\Exception\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class Container implements ContainerInterface
{
    private array $factories = [];
    private array $services = [];
    private array $invokables = [];
    private array $aliases = [];
    private array $shared = [];
    private array $instances = [];

    public function __construct(array $config = [])
    {
        $this->factories  = $config['factories']  ?? [];
        $this->services   = $config['services']   ?? [];
        $this->invokables = $config['invokables'] ?? [];
        $this->aliases    = $config['aliases']    ?? [];
        $this->shared     = $config['shared']     ?? [];
    }

    public function get(string $id): mixed
    {
        $id = $this->aliases[$id] ?? $id;

        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        try {
            $instance = match (true) {
                isset($this->factories[$id])  => $this->createFromFactory($id),
                isset($this->invokables[$id]) => $this->createFromInvokable($id),
                default                        => throw new NotFoundException(
                    sprintf('Service %s not found.', $id)
                )
            };
        } catch (NotFoundExceptionInterface $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ContainerException(
                sprintf('Error while retrieving the entry %s.', $id),
                0,
                $e
            );
        }

        if ($this->shared[$id] ?? false) {
            $this->instances[$id] = $instance;
        }

        return $instance;
    }

    public function has(string $id): bool
    {
        $id = $this->aliases[$id] ?? $id;

        return isset($this->instances[$id])
            || isset($this->services[$id])
            || isset($this->factories[$id])
            || isset($this->invokables[$id]);
    }

    private function createFromFactory(string $id): mixed
    {
        $factory = $this->factories[$id];

        if (is_callable($factory)) {
            return $factory($this, $id);
        }

        if (is_string($factory) && class_exists($factory)) {
            $factoryInstance = new $factory();
            return $factoryInstance($this, $id);
        }

        throw new ContainerException(sprintf('Invalid factory for %s.', $id));
    }

    private function createFromInvokable(string $id): mixed
    {
        $class = $this->invokables[$id];

        if (!class_exists($class)) {
            throw new NotFoundException(sprintf('Invokable class %s not found.', $class));
        }

        return new $class();
    }

    private function isShared(string $id): bool
    {
        return $this->shared[$id] ?? false;
    }
}
