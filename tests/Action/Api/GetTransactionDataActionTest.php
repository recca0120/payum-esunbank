<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Action\Api\GetTransactionDataAction;

class GetTransactionDataActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_get_transaction_data()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $api = m::spy('PayumTW\Esunbank\Api');
        $request = m::spy('PayumTW\Esunbank\Request\Api\GetTransactionData');
        $details = m::mock(new ArrayObject([]));

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $api
            ->shouldReceive('getTransactionData')->andReturn([
                'RC' => '1',
            ]);

        $action = new GetTransactionDataAction();
        $action->setApi($api);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $api->shouldHaveReceived('getTransactionData')->once();
        $details->shouldHaveReceived('replace')->once();
    }
}
