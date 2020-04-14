<?php


namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;


use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class SymfonyContainerAdapterTest extends TestCase
{

    public function testContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('database_host', 'localhost');

        $container->compile();

        $containerAdapter = new SymfonyContainerAdapter($container);

        $this->assertTrue($containerAdapter->has('database_host'));
        $this->assertFalse($containerAdapter->has('not_exists'));
    }

    public function testContainerNotFound()
    {
        $container = new ContainerBuilder();
        $container->compile();
        $containerAdapter = new SymfonyContainerAdapter($container);

        $this->expectException(NotFoundExceptionInterface::class);

        $containerAdapter->get('not_found');
    }

    public function testContainerWithException()
    {
        $container = new ContainerBuilder();
        $definition = new Definition(\stdClass::class);
        $definition->setFactory([self::class, 'exceptionFactory']);
        $container->setDefinition('mydef', $definition);
        $container->compile();
        $containerAdapter = new SymfonyContainerAdapter($container);

        $this->expectException(ContainerExceptionInterface::class);

        $containerAdapter->get('mydef');
    }

    public static function exceptionFactory()
    {
        throw new class('Boom!') extends \Exception implements ContainerExceptionInterface {};
    }

}
