<?php
namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle\Tests\Fixtures;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class TestServiceProviderOverride implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            'stringValue' => function () { return 'foo'; },
        ];
    }

    public static function overrideServiceA(ContainerInterface $container, \stdClass $serviceA = null)
    {
        $serviceA->newProperty = 'foo';
        return $serviceA;
    }

    public function getExtensions()
    {
        return [
            'serviceA' => [ TestServiceProviderOverride::class, 'overrideServiceA' ],
            'stringValue' => function (ContainerInterface $container, $value) {
                return $value . '2';
            },
        ];
    }
}
