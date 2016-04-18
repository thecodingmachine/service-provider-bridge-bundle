<?php
namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle\Tests\Fixtures;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class TestServiceProvider implements ServiceProvider
{
    public function getServices()
    {
        return [
            'serviceA' => function (ContainerInterface $container) {
                $instance = new \stdClass();
                $instance->serviceB = $container->get('serviceB');

                return $instance;
            },
            'serviceB' => [ TestServiceProvider::class, 'createServiceB' ]
        ];
    }

    public static function createServiceB(ContainerInterface $container)
    {
        $instance = new \stdClass();
        // Test getting the database_host parameter.
        $instance->parameter = $container->get('database_host');
        return $instance;
    }
}
