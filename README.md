[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecodingmachine/service-provider-bridge-bundle/badges/quality-score.png?b=1.0)](https://scrutinizer-ci.com/g/thecodingmachine/service-provider-bridge-bundle/?branch=1.0)
[![Build Status](https://travis-ci.org/thecodingmachine/service-provider-bridge-bundle.svg?branch=1.0)](https://travis-ci.org/thecodingmachine/service-provider-bridge-bundle)
[![Coverage Status](https://coveralls.io/repos/thecodingmachine/service-provider-bridge-bundle/badge.svg?branch=1.0&service=github)](https://coveralls.io/github/thecodingmachine/service-provider-bridge-bundle?branch=1.0)


# container-interop/service-provider bridge bundle

Import `service-provider` as defined in `container-interop` into a Symfony application.

## Usage

### Installation

Add `TheCodingMachine\Interop\ServiceProviderBridgeBundle\InteropServiceProviderBridgeBundle` and `\Puli\SymfonyBundle\PuliBundle` in your kernel (the `app/AppKernel.php` file).

**AppKernel.php**
```php
    public function registerBundles()
    {
        $bundles = [
            ...
            new \Puli\SymfonyBundle\PuliBundle(),
            new \TheCodingMachine\Interop\ServiceProviderBridgeBundle\InteropServiceProviderBridgeBundle()
        ];
        ...
    }
```


### Usage using Puli

The bridge bundle will use Puli to automatically discover the service providers of your project. If the service provider you are loading publishes itself
on Puli, then you are done. The services declared in the service provider are available in the Symfony container!

### Usage using manual declaration
 
If the service provider you are using does not publishes itself using Puli, you will have to declare it manually in the constructor of the bundle.

**AppKernel.php**
```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            ...
            new \Puli\SymfonyBundle\PuliBundle(),
            new \TheCodingMachine\Interop\ServiceProviderBridgeBundle\InteropServiceProviderBridgeBundle([
                new MyServiceProvide1(),
                new MyServiceProvide2()
            ])
        ];
        ...
    }
}
```

Alternatively, you can also pass the service provider class name. This is interesting because the service-locator bundle will not instantiate the service provider unless it is needed for a service.
You can therefore improve performances of your application.

**AppKernel.php**
```php
    public function registerBundles()
    {
        $bundles = [
            ...
            new \Puli\SymfonyBundle\PuliBundle(),
            new \TheCodingMachine\Interop\ServiceProviderBridgeBundle\InteropServiceProviderBridgeBundle([
                MyServiceProvide1::class,
                MyServiceProvide2::class
            ])
        ];
        ...
    }
```

Finally, if you need to pass parameters to the constructors of the service providers, you can do this by passing an array:

**AppKernel.php**
```php
    public function registerBundles()
    {
        $bundles = [
            ...
            new \Puli\SymfonyBundle\PuliBundle(),
            new \TheCodingMachine\Interop\ServiceProviderBridgeBundle\InteropServiceProviderBridgeBundle([
                [ MyServiceProvide1::class, [ "param1", "param2" ] ],
                [ MyServiceProvide2::class, [ 42 ] ],
            ])
        ];
        ...
    }
```

## Disabling Puli discovery

You can disable Puli discovery by passing `false` as the second argument of the bundle:

**AppKernel.php**
```php
    public function registerBundles()
    {
        $bundles = [
            ...
            // false is passed as second argument. Puli discovery will be disabled.
            new \TheCodingMachine\Interop\ServiceProviderBridgeBundle\InteropServiceProviderBridgeBundle([
                ...
            ], false)
        ];
        ...
    }
```
