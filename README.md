# container-interop/service-provider bridge bundle

Import `service-provider` as defined in `container-interop` into a Symfony application.

## Usage

### Installation

Add `TheCodingMachine\Interop\ServiceProviderBridgeBundle\InteropServiceProviderBridgeBundle` and `\Puli\SymfonyBundle\PuliBundle` in your kernel (the `app/AppKernel.php` file).

### Usage using Puli

The bridge bundle will use Puli to automatically discover the service providers of your project. If the service provider you are loading publishes itself
on Puli, then you are done. The services declared in the service provider are available in the Symfony container!

### Usage using manual declaration
 
If the service provider you are using does not publishes itself using Puli, you will have to declare it manually in the `interop.service.providers` section of your `parameters.yml` file.

Set the service provider fully qualified class name in the parameter `interop.service.providers`:

```yml
parameters:
  interop.service.providers:
    - \GlideModule\GlideServiceProvider #with mnapoli/glide-module
```

Now, you can do : `$container->get('glide')`

## Disabling Puli discovery

You can disable Puli discovery using the `interop.service.enable_puli` setting:

```yml
parameters:
  # Let's disable Puli discovery:
  interop.service.enable_puli: false
```
