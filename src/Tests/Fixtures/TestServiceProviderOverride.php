<?php
namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle\Tests\Fixtures;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;

class TestServiceProviderOverride implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [];
    }

    public static function overrideServiceA(ContainerInterface $container, \stdClass $serviceA = null)
    {
        $serviceA->newProperty = 'foo';
        return $serviceA;
    }

    public function getExtensions()
    {
        return [
            'serviceA' => [ TestServiceProviderOverride::class, 'overrideServiceA' ]
        ];
    }
}
