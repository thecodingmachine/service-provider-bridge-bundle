# container-interop/service-provider bridge bundle

Import `service-provider` as defined in `container-interop` into a Symfony application.

## Usage

Add `InteropServiceProviderBridgeBundle` in your kernel.

Set the service provider fully qualified class name in the parameter `interop.service.providers`:

```yml
parameters:
  interop.service.providers:
    - \GlideModule\GlideServiceProvider #with mnapoli/glide-module
```

Now, you can do : `$container->get('glide')`
