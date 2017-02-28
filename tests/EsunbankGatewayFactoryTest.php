<?php

namespace PayumTW\Esunbank\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\EsunbankGatewayFactory;

class EsunbankGatewayFactoryTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testCreateConfig()
    {
        $gateway = new EsunbankGatewayFactory();
        $config = $gateway->createConfig([
            'payum.api' => false,
            'payum.required_options' => [],
            'payum.http_client' => $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            'httplug.message_factory' => $messageFactory = m::mock('Http\Message\MessageFactory'),
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ]);

        $this->assertInstanceOf(
            'PayumTW\Esunbank\Api',
            $config['payum.api'](ArrayObject::ensureArrayObject($config))
        );
    }
}
