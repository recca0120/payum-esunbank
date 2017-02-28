<?php

namespace PayumTW\Esunbank\Tests\Api;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpPostRedirect;
use PayumTW\Esunbank\Request\Api\CreateTransaction;
use PayumTW\Esunbank\Action\Api\CreateTransactionAction;

class CreateTransactionActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CreateTransactionAction();
        $request = new CreateTransaction(new ArrayObject([]));

        $action->setApi(
            $api = m::mock('PayumTW\Esunbank\Api')
        );

        $api->shouldReceive('getApiEndpoint')->once()->andReturn($apiEndpoint = 'foo');
        $api->shouldReceive('createTransaction')->once()->with((array) $request->getModel())->andReturn($params = ['foo' => 'bar']);

        try {
            $action->execute($request);
        } catch (HttpPostRedirect $e) {
            $this->assertSame($apiEndpoint, $e->getUrl());
            $this->assertSame($params, $e->getFields());
        }
    }
}
