<?php

namespace TwentyFifth\Payum\WirecardCheckoutPage\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
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
    private $customerId;

    /**
     * @var string
     */
    private $customerSecret;

    /**
     * CaptureAction constructor.
     *
     * @param string $customerId
     * @param string $customerSecret
     */
    public function __construct($customerId, $customerSecret)
    {
        $this->customerId = $customerId;
        $this->customerSecret = $customerSecret;
    }


    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {

        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $payment = $request->getFirstModel();

//        dolog(get_class($payment));
//        dolog(get_class_methods($payment));

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        if (isset($httpRequest->query['cancelled'])) {
            $details['CANCELLED'] = true;
            return;
        }
        if (isset($httpRequest->query['success'])) {
            $details['CAPTURED'] = true;
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


        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_POST           => 1,
            CURLOPT_HEADER         => 1,
            CURLOPT_URL            => 'https://checkout.wirecard.com/page/init.php',
            CURLOPT_FRESH_CONNECT  => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE   => 1,
            CURLOPT_TIMEOUT        => 4,
            CURLOPT_POSTFIELDS     => http_build_query($paymentData)
        ]);


        $http_data = curl_exec($ch); //hit the $url

        $curl_info = curl_getinfo($ch);
        curl_close($ch);
        $headers = substr($http_data, 0, $curl_info["header_size"]);
        preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches);
        $url = $matches[1];

        throw new HttpRedirect($url);

        $renderTemplate = new RenderTemplate($this->templateName, array(
            'paymentData' => $paymentData,
            'url'         => $url,
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