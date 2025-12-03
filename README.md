# marek-baron/container

Lightweight PSR-11 Container implementation without magic.

## Features

- PSR-11 compliant (`Psr\Container\ContainerInterface`)
- Simple configuration via PHP arrays:
    - `services` – prebuilt instances
    - `factories` – callables or factory classes
    - `invokables` – classes instantiated without arguments
    - `aliases` – alternate identifiers for a class
    - `shared` – control which entries are singletons (by default none)
- No magic, no global state, no hidden side effects
- No runtime dependencies, tiny footprint

## Installation

```bash
composer require marek-baron/container
```

## Container example

```php
<?php

use MarekBaron\Container\Container;
use Psr\Container\ContainerInterface;

// Examples
class Logger
{
    public function __construct(
        private readonly array $config,
    ) {}

    public function log(string $message): void
    {
        echo sprintf(
            '[%s] %s | app=%s%s',
            date('c'),
            $message,
            $this->config['app_name'] ?? 'unknown',
            PHP_EOL
        );
    }
}

class LoggerFactory
{
    public function __invoke(ContainerInterface $container, string $id): Logger
    {
        // pull config or other dependencies from the container here
        $config = $container->get('config');
        return new Logger($config);
    }
}

class UserService
{
    public function __construct(
        private Logger $logger,
    ) {}

    public function createUser(string $email): void
    {
        // create user...
        $this->logger->log(sprintf('Created user %s', $email));
    }
}

class SomeSimpleClass
{
    public function foo(): string
    {
        return 'bar';
    }
}

$config = [
    'services' => [
        'config' => [
            'app_name' => 'My App',
        ],
    ],

    'factories' => [
        Logger::class => LoggerFactory::class,
        UserService::class => static function (ContainerInterface $container): UserService {
            return new UserService($container->get(Logger::class));
        },
    ],

    'invokables' => [
        SomeSimpleClass::class => SomeSimpleClass::class,
    ],

    'aliases' => [
        'logger' => Logger::class,
        'user_service' => UserService::class,
    ],

    'shared' => [
        Logger::class => true,       // always the same instance (singleton)
        UserService::class => false, // new instance on each get()
    ],
];

$container = new Container($config);

// Usage examples:
$logger = $container->get(Logger::class); // resolved via factory
$logger = $container->get('logger'); // resolved via alias
$simpleClass = $container->get(SomeSimpleClass::class); // resolved via invokable

$service = $container->get('user_service'); // resolved via alias + factory
$service->createUser('user@example.com');

var_dump($container->has('logger')); // true
var_dump($container->has('unknown_service')); // false

```

## Development (optional)

A Dockerfile and docker-compose.yml are included for local development.
They are excluded from Packagist via .gitattributes.

```bash
docker compose run --rm dev composer check
```

## License

MIT License © Marek Baron


[![CI](https://github.com/marek-baron/container/actions/workflows/ci.yml/badge.svg)](https://github.com/marek-baron/container/actions)
[![Packagist](https://img.shields.io/packagist/v/marek-baron/container.svg)](https://packagist.org/packages/marek-baron/container)
[![License](https://img.shields.io/github/license/marek-baron/container.svg)](LICENSE)
