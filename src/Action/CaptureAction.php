<?php

namespace PayumTW\Esunbank\Action;

use Payum\Core\Request\Capture;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;
use PayumTW\Esunbank\Action\Api\BaseApiAwareAction;
use PayumTW\Esunbank\Request\Api\CreateTransaction;
use Payum\Core\Exception\RequestNotSupportedException;

class CaptureAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        if (isset($httpRequest->request['DATA']) === true) {
            $details->replace($this->api->parseResponse($httpRequest->request['DATA']));

            return;
        }

        $token = $request->getToken();
        $targetUrl = $token->getTargetUrl();

        if (empty($details['U']) === true) {
            $details['U'] = $targetUrl;
        }

        $this->gateway->execute(new CreateTransaction($details));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
