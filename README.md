# Esunbank

[![StyleCI](https://styleci.io/repos/62730948/shield?style=flat)](https://styleci.io/repos/62730948)
[![Build Status](https://travis-ci.org/recca0120/payum-esunbank.svg)](https://travis-ci.org/recca0120/payum-esunbank)
[![Total Downloads](https://poser.pugx.org/payum-tw/esunbank/d/total.svg)](https://packagist.org/packages/payum-tw/esunbank)
[![Latest Stable Version](https://poser.pugx.org/payum-tw/esunbank/v/stable.svg)](https://packagist.org/packages/payum-tw/esunbank)
[![Latest Unstable Version](https://poser.pugx.org/payum-tw/esunbank/v/unstable.svg)](https://packagist.org/packages/payum-tw/esunbank)
[![License](https://poser.pugx.org/payum-tw/esunbank/license.svg)](https://packagist.org/packages/payum-tw/esunbank)
[![Monthly Downloads](https://poser.pugx.org/payum-tw/esunbank/d/monthly)](https://packagist.org/packages/payum-tw/esunbank)
[![Daily Downloads](https://poser.pugx.org/payum-tw/esunbank/d/daily)](https://packagist.org/packages/payum-tw/esunbank)

The Payum extension to rapidly build new extensions.

1. Create new project

```bash
$ composer create-project payum-tw/esunbank
```

2. Replace all occurrences of `payum` with your vendor name. It may be your github name, for now let's say you choose: `esunbank`.
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

    ->getPayum();
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
