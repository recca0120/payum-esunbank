<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use PayumTW\Esunbank\Action\CaptureAction;
use PayumTW\Esunbank\Api;

class CaptureActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_redirect_to_allpay()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new CaptureAction();
        $gateway = m::mock(GatewayInterface::class);
        $request = m::mock(Capture::class);
        $token = m::mock(stdClass::class);
        $api = m::mock(Api::class);
        $model = new ArrayObject([]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $gateway->shouldReceive('execute')->with(GetHttpRequest::class)->once();

        $request
            ->shouldReceive('getModel')->twice()->andReturn($model)
            ->shouldReceive('getToken')->once()->andReturn($token);

        $token
            ->shouldReceive('getTargetUrl')->andReturn('fooOrderResultURL')
            ->shouldReceive('getGatewayName')->andReturn('fooGatewayName')
            ->shouldReceive('getDetails')->andReturn([
                'foo' => 'bar',
            ]);

        $api
            ->shouldReceive('getApiEndpoint')->once()->andReturn('fooApiEndpoint')
            ->shouldReceive('preparePayment')->once()->andReturn($model->toUnsafeArray());

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        $action->setApi($api);
        try {
            $action->execute($request);
        } catch (HttpResponse $response) {
            $this->assertSame('fooApiEndpoint', $response->getUrl());
            $this->assertSame('fooOrderResultURL', $model['U']);
        }
    }

    public function test_captured()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new CaptureAction();
        $gateway = m::mock(GatewayInterface::class);
        $request = m::mock(Capture::class);
        $token = m::mock(stdClass::class);
        $api = m::mock(Api::class);
        $model = new ArrayObject([]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $gateway->shouldReceive('execute')->with(GetHttpRequest::class)->once()->andReturnUsing(function ($request) {
            $request->request = ['DATA' => ['foo' => 'bar']];

            return $request;
        });

        $request->shouldReceive('getModel')->twice()->andReturn($model);

        $api->shouldReceive('parseResult')->once()->andReturn(['foo' => 'bar']);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        $action->setApi($api);
        $action->execute($request);
    }

    /**
     * @expectedException Payum\Core\Exception\UnsupportedApiException
     */
    public function test_api_fail()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new CaptureAction();
        $api = m::mock(stdClass::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setApi($api);
    }
}
