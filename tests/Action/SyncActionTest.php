<?php

namespace PayumTW\Esunbank\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Request\Sync;
use PayumTW\Esunbank\Action\SyncAction;

class SyncActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new SyncAction();
        $request = m::mock(new Sync([]));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $gateway->shouldReceive('execute')->once()->with('PayumTW\Esunbank\Request\Api\getTransactionData');

        $action->execute($request);
    }
}
