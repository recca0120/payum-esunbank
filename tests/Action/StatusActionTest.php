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

    public function test_mark_new()
    {
        $this->validate([], 'markNew');
    }

    public function test_request_mark_captured_by_macd()
    {
        $this->validate([
            'RC' => '00',
            'response' => [
                'MACD' => 'foo',
            ],
        ], 'markCaptured');
    }

    public function test_request_mark_captured_by_air_and_txnamount()
    {
        $this->validate([
            'RC' => '00',
            'AIR' => 'foo',
            'TXNAMOUNT' => 'bar',
        ], 'markCaptured');
    }

    public function test_request_mark_cancel()
    {
        $this->validate([
            'RC' => '00',
            'AIR' => 'foo',
        ], 'markCanceled');
    }

    public function test_request_mark_refund()
    {
        $this->validate([
            'RC' => '00',
        ], 'markRefunded');
    }

    public function test_request_mark_failed()
    {
        $this->validate([
            'RC' => '-1',
        ], 'markFailed');
    }

    protected function validate($input, $type)
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject($input);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details);

        $action = new StatusAction();
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $request->shouldHaveReceived($type)->once();
    }
}
