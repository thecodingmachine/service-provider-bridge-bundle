<?php


namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;

use Interop\Container\Factories\Alias;
use Interop\Container\ServiceProviderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Twig_Environment;

/**
 * Provides common aliases for Symfony services.
 * For instance : logger => Psr\Logger\LoggerInterface
 */
class CommonAliasesServiceProvider implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            LoggerInterface::class => new Alias('logger'),
            CacheItemPoolInterface::class => new Alias('cache.app'),
            Twig_Environment::class => new Alias('twig')
        ];
    }

    public function getExtensions()
    {
        return [];
    }
}
