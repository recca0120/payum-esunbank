<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Action\CancelAction;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Core\Request\Cancel;
use PayumTW\Esunbank\Request\Api\CancelTransaction;

class CancelActionTest extends PHPUnit_Framework_TestCase
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

        $action = new CancelAction();
        $gateway = m::mock(GatewayInterface::class);
        $request = m::mock(Cancel::class);
        $details = new ArrayObject();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details)->twice();

        $gateway
            ->shouldReceive('execute')->with(m::type(CancelTransaction::class))
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
