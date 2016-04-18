<?php
namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle\Tests\Fixtures;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class TestServiceProviderOverride implements ServiceProvider
{
    public function getServices()
    {
        return [
            'serviceA' => [ TestServiceProviderOverride::class, 'overrideServiceA' ]
        ];
    }

    public static function overrideServiceA(ContainerInterface $container, callable $previousCallback = null)
    {
        $serviceA = $previousCallback();
        $serviceA->newProperty = 'foo';
        return $serviceA;
    }

}
