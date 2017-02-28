<?php

namespace PayumTW\Esunbank\Tests\Api;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Request\Api\CancelTransaction;
use PayumTW\Esunbank\Action\Api\CancelTransactionAction;

class CancelTransactionActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CancelTransactionAction();
        $request = new CancelTransaction(new ArrayObject(['ONO' => 'foo']));

        $action->setApi(
            $api = m::mock('PayumTW\Esunbank\Api')
        );

        $api->shouldReceive('cancelTransaction')->once()->with((array) $request->getModel())->andReturn($params = ['foo' => 'bar']);

        $action->execute($request);

        $this->assertSame(array_merge(['ONO' => 'foo'], $params), (array) $request->getModel());
    }
}
