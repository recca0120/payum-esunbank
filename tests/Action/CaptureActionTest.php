<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Request\GetHttpRequest;
use PayumTW\Esunbank\Action\CaptureAction;
use PayumTW\Esunbank\Api;
use PayumTW\Esunbank\Request\Api\CreateTransaction;

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
        | Set
        |------------------------------------------------------------
        */

        $action = new CaptureAction();
        $gateway = m::mock(GatewayInterface::class);
        $request = m::mock(Capture::class);
        $token = m::mock(stdClass::class);
        $details = new ArrayObject([]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $gateway
            ->shouldReceive('execute')->with(m::type(GetHttpRequest::class))->once()
            ->shouldReceive('execute')->with(m::type(CreateTransaction::class))->once();

        $request
            ->shouldReceive('getModel')->twice()->andReturn($details)
            ->shouldReceive('getToken')->once()->andReturn($token);

        $token
            ->shouldReceive('getTargetUrl')->andReturn('fooOrderResultURL');

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        $action->execute($request);
        $this->assertSame([
            'U' => 'fooOrderResultURL',
        ], (array) $details);
    }

    public function test_captured()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new CaptureAction();
        $gateway = m::mock(GatewayInterface::class);
        $request = m::mock(Capture::class);
        $token = m::mock(stdClass::class);
        $api = m::mock(Api::class);
        $details = new ArrayObject([]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $gateway
            ->shouldReceive('execute')->with(GetHttpRequest::class)->once()->andReturnUsing(function ($request) {
                $request->request = ['DATA' => ['foo' => 'bar']];

                return $request;
            })
            ->shouldReceive('execute')->with(m::type(Sync::class))->once();

        $request->shouldReceive('getModel')->twice()->andReturn($details);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        $action->execute($request);
    }
}
