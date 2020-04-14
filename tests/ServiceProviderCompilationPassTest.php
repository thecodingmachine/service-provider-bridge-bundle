<?php


namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;


use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use TheCodingMachine\Interop\ServiceProviderBridgeBundle\Tests\Fixtures\TestServiceProvider;
use TheCodingMachine\Interop\ServiceProviderBridgeBundle\Tests\Fixtures\TestServiceProviderOverride;
use TheCodingMachine\Interop\ServiceProviderBridgeBundle\Tests\Fixtures\TestServiceProviderOverride2;

class ServiceProviderCompilationPassTest extends \PHPUnit_Framework_TestCase
{
    protected function getContainer(array $lazyArray, $useDiscovery = false)
    {
        $bundle = new InteropServiceProviderBridgeBundle($lazyArray, $useDiscovery);

        $container = new ContainerBuilder();
        $container->setParameter('database_host', 'localhost');
        $container->setDefinition('logger', new Definition(NullLogger::class));

        $bundle->build($container);
        $container->compile();
        $bundle->setContainer($container);
        $bundle->boot();
        return $container;
    }

    public function testSimpleServiceProvider()
    {
        $container = $this->getContainer([
            TestServiceProvider::class
        ]);

        $serviceA = $container->get('serviceA');

        $this->assertInstanceOf(\stdClass::class, $serviceA);
        $this->assertEquals(42, $container->get('function'));
    }

    public function testServiceProviderOverrides()
    {
        $container = $this->getContainer([
            TestServiceProvider::class,
            TestServiceProviderOverride::class,
            TestServiceProviderOverride2::class
        ]);

        $serviceA = $container->get('serviceA');

        $this->assertInstanceOf(\stdClass::class, $serviceA);
        $this->assertEquals('foo', $serviceA->newProperty);
        $this->assertEquals('bar', $serviceA->newProperty2);
    }

    public function testExtensionsAreCalledInCorrectOrder()
    {
        $container = $this->getContainer([
            new TestServiceProvider(),
            new TestServiceProviderOverride(),
        ]);

        $value = $container->get('stringValue');

        $this->assertSame('foo12', $value);
    }

    /**
     * @expectedException \TheCodingMachine\Interop\ServiceProviderBridgeBundle\Exception\InvalidArgumentException
     */
    /*public function testExceptionMessageIfNoPuliBundle()
    {
        $bundle = new InteropServiceProviderBridgeBundle([], true);
        $container = new ContainerBuilder();
        $bundle->build($container);
        $container->compile();
    }*/

    /**
     *
     */
    public function testTcmDiscovery()
    {
        // If TCM discovery is enabled, the CommonAliasesServiceProvider is registered.
        $container = $this->getContainer([], true);

        $logger = $container->get(LoggerInterface::class);

        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }
}
