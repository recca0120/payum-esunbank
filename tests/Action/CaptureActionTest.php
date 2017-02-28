<?php

namespace PayumTW\Esunbank\Tests;

use Mockery as m;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use Payum\Core\Request\GetHttpRequest;
use PayumTW\Esunbank\Action\CaptureAction;

class CaptureActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CaptureAction();
        $request = m::mock(new Capture([]));
        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $gateway->shouldReceive('execute')->once()->with(m::on(function ($httpRequest) {
            return $httpRequest instanceof GetHttpRequest;
        }));

        $request->shouldReceive('getToken')->once()->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );
        $token->shouldReceive('getTargetUrl')->once()->andReturn($targetUrl = 'foo');

        $gateway->shouldReceive('execute')->once()->with(m::type('PayumTW\Esunbank\Request\Api\CreateTransaction'));
        $action->execute($request);
    }

    public function testCaptured()
    {
        $action = new CaptureAction();
        $request = m::mock(new Capture([]));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $query = [
            'DATA' => 'foo',
            'MACD' => 'foo',
            'RC' => '00',
        ];

        $gateway->shouldReceive('execute')->once()->with(m::on(function ($httpRequest) use ($query) {
            $httpRequest->request = $query;

            return $httpRequest instanceof GetHttpRequest;
        }));

        $action->setApi(
            $api = m::mock('PayumTW\Esunbank\Api')
        );

        $api->shouldReceive('parseResponse')->once()->with($query)->andReturn($query);
        $api->shouldReceive('verifyHash')->once()->with($query['MACD'], m::any())->andReturn(true);

        $action->execute($request);
        $this->assertSame($query, (array) $request->getModel());
    }

    public function testCaptureFail()
    {
        $action = new CaptureAction();
        $request = m::mock(new Capture([]));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $query = [
            'DATA' => 'foo',
            'MACD' => 'foo',
            'RC' => '00',
        ];

        $gateway->shouldReceive('execute')->once()->with(m::on(function ($httpRequest) use ($query) {
            $httpRequest->request = $query;

            return $httpRequest instanceof GetHttpRequest;
        }));

        $action->setApi(
            $api = m::mock('PayumTW\Esunbank\Api')
        );

        $api->shouldReceive('parseResponse')->once()->with($query)->andReturn($query);
        $api->shouldReceive('verifyHash')->once()->with($query['MACD'], m::any())->andReturn(false);

        $action->execute($request);
        $this->assertSame(array_merge($query, ['RC' => '-1']), (array) $request->getModel());
    }
}
