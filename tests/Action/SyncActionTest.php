<?php

namespace PayumTW\Esunbank\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
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
        $request = new Sync(new ArrayObject([]));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $gateway->shouldReceive('execute')->once()->with('PayumTW\Esunbank\Request\Api\getTransactionData');

        $action->execute($request);
    }
}
