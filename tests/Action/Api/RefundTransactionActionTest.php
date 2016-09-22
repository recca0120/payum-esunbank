<?php

use Mockery as m;
use PayumTW\Esunbank\Action\Api\RefundTransactionAction;
use PayumTW\Esunbank\Api;
use PayumTW\Esunbank\Request\Api\RefundTransaction;
use Payum\Core\Bridge\Spl\ArrayObject;

class RefundTransactionActionTest extends PHPUnit_Framework_TestCase
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
        $request = m::mock(RefundTransaction::class);
        $details = new ArrayObject(['ONO' => 'foo']);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->twice()->andReturn($details);

        $api->shouldReceive('refundTransaction')->with(['ONO' => 'foo'])->once()->andReturn($details);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action = new RefundTransactionAction();
        $action->setApi($api);
        $action->execute($request);
    }
}
