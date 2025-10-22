<?php

/**
 * Author: Marek Baron
 * GitHub: https://www.github.com/marek-baron
 * Project: marek-baron/dependency-injector
 */

declare(strict_types=1);

namespace MarekBaron\Container;

use Psr\Container\ContainerInterface;
use RuntimeException;

class Container implements ContainerInterface
{
    private ?ResolverInterface $resolver = null;
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

        $instance = match (true) {
            isset($this->factories[$id])  => $this->createFromFactory($id),
            isset($this->invokables[$id]) => $this->createFromInvokable($id),
            $this->resolver !== null       => $this->resolver->resolve($this, $id),
            default                        => throw new RuntimeException("Service '{$id}' not found.")
        };

        if ($this->shared[$id] ?? true) {
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
            || isset($this->invokables[$id])
            || class_exists($id);
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

        throw new RuntimeException("Invalid factory for '{$id}'");
    }

    private function createFromInvokable(string $id): mixed
    {
        $class = $this->invokables[$id];

        if (!class_exists($class)) {
            throw new RuntimeException("Invokable class '{$class}' not found.");
        }

        return new $class();
    }

    private function isShared(string $id): bool
    {
        return $this->shared[$id] ?? true;
    }

    public function setResolver(?ResolverInterface $resolver): void
    {
        $this->resolver = $resolver;
    }
}
