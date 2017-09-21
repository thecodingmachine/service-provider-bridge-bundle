<?php


namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use TheCodingMachine\Discovery\DiscoveryInterface;
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

        $bundle->build($container);
        $container->compile();
        $bundle->setContainer($container);
        $bundle->boot();
        return $container;
    }

    /*protected function getDiscovery()
    {
        $discovery = new InMemoryDiscovery();
        $discovery->addBindingType(new BindingType('container-interop/service-provider'));
        $classBinding = new ClassBinding(TestServiceProvider::class, 'container-interop/service-provider');
        $discovery->addBinding($classBinding);
        return $discovery;
    }*/

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
    /*public function testPuliBundle()
    {
        $container = $this->getContainer([], true);

        $serviceA = $container->get('serviceA');

        $this->assertInstanceOf(\stdClass::class, $serviceA);
    }*/
}
