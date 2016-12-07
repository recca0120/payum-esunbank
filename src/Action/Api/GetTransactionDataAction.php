<?php

namespace PayumTW\Esunbank\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Request\Api\GetTransactionData;
use Payum\Core\Exception\RequestNotSupportedException;

class GetTransactionDataAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request GetTransactionData
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $result = $this->api->getTransactionData((array) $details);

        if (isset($result['RC']) === true && $result['RC'] === '-1') {
            return;
        }

        $details->replace($result);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetTransactionData &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
