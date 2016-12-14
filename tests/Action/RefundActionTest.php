<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Action\RefundAction;

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
        | Arrange
        |------------------------------------------------------------
        */

        $gateway = m::spy('Payum\Core\GatewayInterface');
        $request = m::spy('Payum\Core\Request\Refund');
        $details = new ArrayObject();

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $action = new RefundAction();
        $action->setGateway($gateway);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('PayumTW\Esunbank\Request\Api\RefundTransaction'))->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\Sync'))->once();
    }
}
