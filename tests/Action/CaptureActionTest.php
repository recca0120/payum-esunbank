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
        | Set
        |------------------------------------------------------------
        */

        $action = new CaptureAction();
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $request = m::mock('Payum\Core\Request\Capture');
        $token = m::mock('stdClass');
        $details = new ArrayObject([]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once()
            ->shouldReceive('execute')->with(m::type('PayumTW\Esunbank\Request\Api\CreateTransaction'))->once();

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
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $request = m::mock('Payum\Core\Request\Capture');
        $token = m::mock('stdClass');
        $api = m::mock('PayumTW\Esunbank\Api');
        $details = new ArrayObject([]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $gateway
            ->shouldReceive('execute')->with('Payum\Core\Request\GetHttpRequest')->once()->andReturnUsing(function ($request) {
                $request->request = ['DATA' => ['foo' => 'bar']];

                return $request;
            })
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\Sync'))->once();

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
