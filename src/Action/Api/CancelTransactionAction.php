<?php

namespace PayumTW\Esunbank\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Request\Api\CancelTransaction;
use Payum\Core\Exception\RequestNotSupportedException;

class CancelTransactionAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request RefundTransaction
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $details->validateNotEmpty(['ONO']);

        $details->replace($this->api->cancelTransaction((array) $details));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CancelTransaction &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
