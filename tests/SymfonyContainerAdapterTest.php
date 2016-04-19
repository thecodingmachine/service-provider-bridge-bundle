<?php


namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class SymfonyContainerAdapterTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @expectedException \TheCodingMachine\Interop\ServiceProviderBridgeBundle\Exception\NotFoundException
     */
    public function testContainerNotFound()
    {
        $container = new ContainerBuilder();
        $container->compile();
        $containerAdapter = new SymfonyContainerAdapter($container);

        $containerAdapter->get('not_found');
    }

    /**
     * @expectedException \TheCodingMachine\Interop\ServiceProviderBridgeBundle\Exception\ContainerException
     */
    public function testContainerWithException()
    {
        $container = new ContainerBuilder();
        $definition = new Definition(\stdClass::class);
        $definition->setFactory(self::class, "exceptionFactory");
        $container->setDefinition('mydef', $definition);
        $container->compile();
        $containerAdapter = new SymfonyContainerAdapter($container);

        $containerAdapter->get('mydef');
    }

    public static function exceptionFactory()
    {
        throw new \Exception('Boom!');
    }

}
