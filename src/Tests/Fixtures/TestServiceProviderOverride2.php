<?php

namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle\Tests\Fixtures;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;

class TestServiceProviderOverride2 implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [];
    }

    public static function overrideServiceA(ContainerInterface $container, $serviceA = null)
    {
        $serviceA->newProperty2 = 'bar';

        return $serviceA;
    }

    public function getExtensions()
    {
        return [
            'serviceA' => [self::class, 'overrideServiceA'],
        ];
    }
}
