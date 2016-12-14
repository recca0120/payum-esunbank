<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Action\SyncAction;

class SyncActionTest extends PHPUnit_Framework_TestCase
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
        $request = m::spy('Payum\Core\Request\Sync');
        $details = new ArrayObject();
        $getHttpRequest = m::spy('Payum\Core\Request\GetHttpRequest');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $action = new SyncAction();
        $action->setGateway($gateway);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('PayumTW\Esunbank\Request\Api\GetTransactionData'))->once();
    }
}
