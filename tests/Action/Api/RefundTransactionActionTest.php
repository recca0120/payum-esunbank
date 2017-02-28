<?php

namespace PayumTW\Esunbank\Tests\Action\Api;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Request\Api\RefundTransaction;
use PayumTW\Esunbank\Action\Api\RefundTransactionAction;

class RefundTransactionActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new RefundTransactionAction();
        $request = new RefundTransaction(new ArrayObject(['ONO' => 'foo']));

        $action->setApi(
            $api = m::mock('PayumTW\Esunbank\Api')
        );

        $api->shouldReceive('refundTransaction')->once()->with((array) $request->getModel())->andReturn($params = ['foo' => 'bar']);

        $action->execute($request);

        $this->assertSame(array_merge(['ONO' => 'foo'], $params), (array) $request->getModel());
    }
}
