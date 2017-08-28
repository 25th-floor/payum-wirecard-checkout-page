<?php

namespace TwentyFifth\Payum\WirecardCheckoutPage\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;

class CaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait, GenericTokenFactoryAwareTrait;

    /**
     * @var string
     */
    private $templateName;

    /**
     * CaptureAction constructor.
     *
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        dolog(__METHOD__);

        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $payment = $request->getFirstModel();

        dolog(get_class($payment));
        dolog(get_class_methods($payment));

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        dolog($httpRequest);
        if (isset($httpRequest->query['cancelled'])) {
            $details['CANCELLED'] = true;
            dolog('cancel');
            return;
        }
        if (isset($httpRequest->query['success'])) {
            $details['SUCCESS'] = true;
            dolog('success');
            return;
        }

        $paymentData = [
            'customerId'       => 'D200411',
            'paymentType'      => 'CCARD',
            'amount'           => ($payment->getAmount() / 100),
            'currency'         => $payment->getCurrencyCode(),
            'language'         => 'de',
            'orderDescription' => 'testOrderDescription',
            'successUrl'       => $request->getToken()->getTargetUrl() . '?success=1',
            'cancelUrl'        => $request->getToken()->getTargetUrl() . '?cancelled=1',
            'failureUrl'       => $request->getToken()->getTargetUrl() . '?failure=1',
            'serviceUrl'       => 'http://localhost',
            'confirmUrl'       => 'https://requestb.in/18w0jkr1',
        ];
        $paymentData['requestFingerprintOrder'] = $this->getRequestFingerprintOrder($paymentData);
        $paymentData['requestFingerprint'] = $this->getRequestFingerprint($paymentData,
            'CHCSH7UGHVVX2P7EHDHSY4T2S4CGYK4QBE4M5YUUG2ND5BEZWNRZW5EJYVJQ');


        $renderTemplate = new RenderTemplate($this->templateName, array(
            'paymentData' => $paymentData,
        ));
        $this->gateway->execute($renderTemplate);

        throw new HttpResponse($renderTemplate->getResult());

    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess;
    }


    private function getRequestFingerprintOrder(array $theParams)
    {
        $ret = "";
        foreach ($theParams as $key => $value) {
            $ret .= "$key,";
        }
        $ret .= "requestFingerprintOrder,secret";
        return $ret;
    }

    private function getRequestFingerprint(array $theParams, string $theSecret)
    {
        $ret = "";
        foreach ($theParams as $key => $value) {
            $ret .= "$value";
        }
        $ret .= "$theSecret";
        return hash_hmac("sha512", $ret, $theSecret);
    }
}