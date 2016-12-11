<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Action\Api\CancelTransactionAction;

class CancelTransactionActionTest extends PHPUnit_Framework_TestCase
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

        $request = m::spy('PayumTW\Esunbank\Request\Api\CancelTransaction, ArrayAccess');
        $api = m::spy('PayumTW\Esunbank\Api');
        $input = [
            'ONO' => 'foo'
        ];
        $details = new ArrayObject($input);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $api
            ->shouldReceive('cancelTransaction')->andReturn($details);

        $action = new CancelTransactionAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $action->execute($request);
        $request->shouldHaveReceived('getModel')->twice();
        $api->shouldHaveReceived('cancelTransaction')->with($input)->once();
    }

    /**
     * @expectedException Payum\Core\Exception\UnsupportedApiException
     */
    public function test_throw_exception_when_api_is_error()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $api = m::spy('stdClass');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $action = new CancelTransactionAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */
    }
}
