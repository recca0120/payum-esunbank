<?php

namespace PayumTW\Esunbank\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (isset($details['RC']) === false) {
            $request->markNew();

            return;
        }

        if ($details['RC'] === '00') {
            if (isset($details['response']) === true && isset($details['response']['MACD'])) {
                $request->markCaptured();

                return;
            }

            // 單筆查詢
            if (isset($details['AIR']) === true && isset($details['TXNAMOUNT']) === true) {
                $request->markCaptured();

                return;
            }

            if (isset($details['AIR']) === true) {
                $request->markCanceled();

                return;
            }

            $request->markRefunded();

            return;
        }

        $request->markFailed();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
