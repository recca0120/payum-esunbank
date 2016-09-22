<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use PayumTW\Esunbank\Action\RefundAction;
use PayumTW\Esunbank\Request\Api\RefundTransaction;

class RefundActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_execute()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new RefundAction();
        $gateway = m::mock(GatewayInterface::class);
        $request = m::mock(Refund::class);
        $details = new ArrayObject();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details)->twice();

        $gateway
            ->shouldReceive('execute')->with(m::type(RefundTransaction::class))
            ->shouldReceive('execute')->with(m::type(Sync::class));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        $action->execute($request);
    }
}
