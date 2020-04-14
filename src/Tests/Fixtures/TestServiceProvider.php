<?php
namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle\Tests\Fixtures;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

function myFunctionFactory()
{
    return 42;
}

class TestServiceProvider implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            'serviceA' => function (ContainerInterface $container) {
                $instance = new \stdClass();
                $instance->serviceB = $container->get('serviceB');

                return $instance;
            },
            'serviceB' => [ TestServiceProvider::class, 'createServiceB' ],
            'function' => 'TheCodingMachine\\Interop\\ServiceProviderBridgeBundle\\Tests\\Fixtures\\myFunctionFactory'
        ];
    }

    public static function createServiceB(ContainerInterface $container)
    {
        $instance = new \stdClass();
        // Test getting the database_host parameter.
        $instance->parameter = $container->get('database_host');
        return $instance;
    }

    public function getExtensions()
    {
        return [
            'stringValue' => function (ContainerInterface $container, $value) {
                return $value . '1';
            },
        ];
    }
}
