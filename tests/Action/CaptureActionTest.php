<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Action\CaptureAction;

class CaptureActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_redirect_to_esunbank()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $gateway = m::spy('Payum\Core\GatewayInterface');
        $request = m::spy('Payum\Core\Request\Capture');
        $token = m::spy('Payum\Core\Model\TokenInterface');
        $details = new ArrayObject([]);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)
            ->shouldReceive('getToken')->andReturn($token);

        $token
            ->shouldReceive('getTargetUrl')->andReturn('fooOrderResultURL');

        $action = new CaptureAction();
        $action->setGateway($gateway);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame([
            'U' => 'fooOrderResultURL',
        ], (array) $details);

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $request->shouldHaveReceived('getToken')->once();
        $token->shouldHaveReceived('getTargetUrl')->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('PayumTW\Esunbank\Request\Api\CreateTransaction'))->once();
    }

    public function test_captured()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $api = m::spy('PayumTW\Esunbank\Api');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $request = m::spy('Payum\Core\Request\Capture');
        $token = m::spy('Payum\Core\Model\TokenInterface');
        $details = new ArrayObject([]);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with('Payum\Core\Request\GetHttpRequest')->once()->andReturnUsing(function ($request) {
                $request->request = ['DATA' => ['foo' => 'bar']];

                return $request;
            });

        $api->shouldReceive('parseResponse')->andReturn([]);

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gateway);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
    }
}
