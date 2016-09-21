<?php

namespace PayumTW\Esunbank\Action\Api;

use PayumTW\Esunbank\Request\Api\CreateTransaction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpPostRedirect;

class CreateTransactionAction extends BaseApiAwareAction{

    /**
     * {@inheritdoc}
     *
     * @param $request CreateTransaction
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        throw new HttpPostRedirect(
            $this->api->getApiEndpoint(),
            $this->api->createTransaction($details->toUnsafeArray())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateTransaction &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
