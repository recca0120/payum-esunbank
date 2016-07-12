# Esunbank

The Payum extension to rapidly build new extensions.

1. Create new project

```bash
$ composer create-project payum-tw/esunbank
```

2. Replace all occurrences of `payum` with your vendor name. It may be your github name, for now let's say you choose: `acme`.
3. Replace all occurrences of `esunbank` with a payment gateway name. For example Stripe, Paypal etc. For now let's say you choose: `esunbank`.
4. Register a gateway factory to the payum's builder and create a gateway:

```php
<?php

use Payum\Core\PayumBuilder;
use Payum\Core\GatewayFactoryInterface;

$defaultConfig = [];

$payum = (new PayumBuilder)
    ->addGatewayFactory('esunbank', function(array $config, GatewayFactoryInterface $coreGatewayFactory) {
        return new \PayumTW\Esunbank\EsunbankGatewayFactory($config, $coreGatewayFactory);
    })

    ->addGateway('esunbank', [
        'factory' => 'esunbank',
        // 特店代碼
        'MID'     => '',
        // MAC KEY
        'M'       => '',
        'sandbox' => true,
    ])

    ->getPayum()
;
```

5. While using the gateway implement all method where you get `Not implemented` exception:

```php
<?php

use Payum\Core\Request\Capture;

$esunbank = $payum->getGateway('esunbank');

$model = new \ArrayObject([
  // ...
]);

$esunbank->execute(new Capture($model));
```

## Resources

* [Documentation](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/index.md)
* [Questions](http://stackoverflow.com/questions/tagged/payum)
* [Issue Tracker](https://github.com/Payum/Payum/issues)
* [Twitter](https://twitter.com/payumphp)

## License

Skeleton is released under the [MIT License](LICENSE).
