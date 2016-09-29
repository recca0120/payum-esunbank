<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Action\StatusAction;

class StatusActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_request_mark_new()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $model = new ArrayObject();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($model)->twice()
            ->shouldReceive('markNew')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }

    public function test_request_mark_captured_by_macd()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $model = new ArrayObject([
            'RC' => '00',
            'response' => [
                'MACD' => 'foo',
            ],
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($model)->twice()
            ->shouldReceive('markCaptured')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }

    public function test_request_mark_captured_by_air_and_txnamount()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $model = new ArrayObject([
            'RC' => '00',
            'AIR' => 'foo',
            'TXNAMOUNT' => 'bar',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($model)->twice()
            ->shouldReceive('markCaptured')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }

    public function test_request_mark_cancel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $model = new ArrayObject([
            'RC' => '00',
            'AIR' => 'foo',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($model)->twice()
            ->shouldReceive('markCanceled')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }

    public function test_request_mark_refund()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $model = new ArrayObject([
            'RC' => '00',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($model)->twice()
            ->shouldReceive('markRefunded')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }

    public function test_request_mark_failed()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $model = new ArrayObject([
            'RC' => '-1',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($model)->twice()
            ->shouldReceive('markFailed')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }
}
