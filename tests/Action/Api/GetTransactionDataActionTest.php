<?php

namespace PayumTW\Esunbank\Tests\Api;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Request\Api\GetTransactionData;
use PayumTW\Esunbank\Action\Api\GetTransactionDataAction;

class GetTransactionDataActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new GetTransactionDataAction();
        $request = new GetTransactionData(new ArrayObject([]));

        $action->setApi(
            $api = m::mock('PayumTW\Esunbank\Api')
        );

        $api->shouldReceive('getTransactionData')->once()->with((array) $request->getModel())->andReturn($params = ['foo' => 'bar']);

        $action->execute($request);

        $this->assertSame($params, (array) $request->getModel());
    }
}
