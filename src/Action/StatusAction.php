<?php

namespace PayumTW\Esunbank\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;

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
            if (isset($details['MACD']) === true && isset($details['RRN']) === true) {
                $request->markCaptured();

                return;
            }

            // 單筆查詢
            if (isset($details['RRN']) === true && isset($details['TXNAMOUNT']) === true) {
                $request->markCaptured();

                return;
            }

            if (isset($details['RRN']) === true) {
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
