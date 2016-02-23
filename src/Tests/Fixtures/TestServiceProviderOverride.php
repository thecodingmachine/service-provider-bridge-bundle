<?php
namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle\Tests\Fixtures;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class TestServiceProviderOverride implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'serviceA' => 'overrideServiceA'
        ];
    }

    public static function overrideServiceA(ContainerInterface $container, $serviceA)
    {
        $serviceA->newProperty = 'foo';
        return $serviceA;
    }

}
