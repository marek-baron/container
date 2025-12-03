<?php

/**
 * Author: Marek Baron
 * GitHub: https://www.github.com/marek-baron
 * Project: marek-baron/container
 */

declare(strict_types=1);

namespace MarekBaron\Test\Container;

use MarekBaron\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use stdClass;

class ContainerTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsPredefinedService(): void
    {
        $service = new stdClass();
        $container = new Container(['services' => ['foo' => $service]]);

        $this->assertSame($service, $container->get('foo'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreatesInstanceFromFactoryCallable(): void
    {
        $container = new Container([
            'factories' => [
                'foo' => fn ($c, $id) => new stdClass()
            ]
        ]);

        $result = $container->get('foo');
        $this->assertInstanceOf(stdClass::class, $result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreatesInstanceFromFactoryClassName(): void
    {
        $container = new Container([
            'factories' => [
                'foo' => DummyFactory::class
            ]
        ]);

        $result = $container->get('foo');
        $this->assertInstanceOf(stdClass::class, $result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreatesInstanceFromInvokable(): void
    {
        $container = new Container([
            'invokables' => [
                'foo' => DummyInvokable::class
            ]
        ]);

        $this->assertInstanceOf(DummyInvokable::class, $container->get('foo'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAliasResolvesToTarget(): void
    {
        $service = new stdClass();
        $container = new Container([
            'services' => ['target' => $service],
            'aliases' => ['alias' => 'target']
        ]);

        $this->assertSame($service, $container->get('alias'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSharedInstanceIsCached(): void
    {
        $container = new Container([
            'invokables' => ['foo' => DummyInvokable::class],
            'shared' => ['foo' => true]
        ]);

        $first = $container->get('foo');
        $second = $container->get('foo');
        $this->assertSame($first, $second);
    }

    public function testHasReturnsTrueWhenAnySourceMatches(): void
    {
        $container = new Container([
            'services' => ['foo' => new stdClass()],
            'factories' => ['bar' => fn () => null],
            'invokables' => ['baz' => DummyInvokable::class],
            'aliases' => ['alias' => 'foo']
        ]);

        $this->assertTrue($container->has('foo'));
        $this->assertTrue($container->has('bar'));
        $this->assertTrue($container->has('baz'));
        $this->assertTrue($container->has('alias'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testThrowsWhenServiceNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service missing not found.');

        $container = new Container();
        $container->get('missing');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testThrowsWhenInvalidFactory(): void
    {
        $container = new Container(['factories' => ['foo' => 123]]);
        $this->expectException(RuntimeException::class);
        $container->get('foo');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testThrowsWhenInvokableClassNotFound(): void
    {
        $container = new Container(['invokables' => ['foo' => 'NonExistingClass']]);
        $this->expectException(RuntimeException::class);
        $container->get('foo');
    }
}
