<?php

namespace PayumTW\Esunbank\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Request\Refund;
use PayumTW\Esunbank\Action\RefundAction;

class RefundActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new RefundAction();
        $request = m::mock(new Refund([]));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $gateway->shouldReceive('execute')->once()->with('PayumTW\Esunbank\Request\Api\RefundTransaction');

        $action->execute($request);
    }
}
