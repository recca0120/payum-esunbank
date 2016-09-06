<?php

use Http\Message\MessageFactory;
use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\HttpClientInterface;
use PayumTW\Esunbank\Api;
use PayumTW\Esunbank\EsunbankGatewayFactory;

class EsunbankGatewayFactoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_create_factory()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $httpClient = m::mock(HttpClientInterface::class);
        $message = m::mock(MessageFactory::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $gateway = new EsunbankGatewayFactory();
        $config = $gateway->createConfig([
            'payum.api' => false,
            'payum.required_options' => [],
            'payum.http_client' => $httpClient,
            'httplug.message_factory' => $message,
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ]);

        $api = call_user_func($config['payum.api'], ArrayObject::ensureArrayObject($config));
        $this->assertInstanceOf(Api::class, $api);
    }
}
