<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Action\Api\CancelTransactionAction;
use PayumTW\Esunbank\Api;
use PayumTW\Esunbank\Request\Api\CancelTransaction;

class CancelTransactionActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_get_transaction_data()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $api = m::mock(Api::class);
        $request = m::mock(CancelTransaction::class);
        $details = new ArrayObject(['ONO' => 'foo']);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->twice()->andReturn($details);

        $api->shouldReceive('cancelTransaction')->with(['ONO' => 'foo'])->once()->andReturn($details);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action = new CancelTransactionAction();
        $action->setApi($api);
        $action->execute($request);
    }
}
