<?php

namespace PayumTW\Esunbank\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Request\Cancel;
use PayumTW\Esunbank\Action\CancelAction;

class CancelActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CancelAction();
        $request = m::mock(new Cancel([]));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $gateway->shouldReceive('execute')->once()->with('PayumTW\Esunbank\Request\Api\CancelTransaction');

        $action->execute($request);
    }
}
