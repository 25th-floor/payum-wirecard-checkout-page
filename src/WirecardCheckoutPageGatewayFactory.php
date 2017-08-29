<?php

namespace TwentyFifth\Payum\WirecardCheckoutPage;

use TwentyFifth\Payum\WirecardCheckoutPage\Action\AuthorizeAction;
use TwentyFifth\Payum\WirecardCheckoutPage\Action\CancelAction;
use TwentyFifth\Payum\WirecardCheckoutPage\Action\CaptureAction;
use TwentyFifth\Payum\WirecardCheckoutPage\Action\ConvertPaymentAction;
use TwentyFifth\Payum\WirecardCheckoutPage\Action\NotifyAction;
use TwentyFifth\Payum\WirecardCheckoutPage\Action\RefundAction;
use TwentyFifth\Payum\WirecardCheckoutPage\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

require_once __DIR__ . '/dev_logging.php';

class WirecardCheckoutPageGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {


        $config->defaults([
            'payum.template.authorize' => '@TwentyFifthPayumWirecardCheckoutPage/Action/capture.html.twig',
        ]);

        $config->defaults([
            'payum.factory_name'           => 'wirecard_checkout_page',
            'payum.factory_title'          => 'Wirecard Checkout Page',
            'payum.action.capture'         => new CaptureAction(
                'nix', 'nix'
            ),
            'payum.action.authorize'       => new AuthorizeAction(),
            'payum.action.refund'          => new RefundAction(),
            'payum.action.cancel'          => new CancelAction(),
            'payum.action.notify'          => new NotifyAction(),
            'payum.action.status'          => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [
//                'id',
//                'secret',
//                'sandbox'
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);
                /*print_r(array_keys($config->getArrayCopy()));
                die();*/
                return new Api((array)$config, $config['payum.http_client'], $config['httplug.message_factory']);
            };


        }

        $config['payum.paths'] = array_replace([
            'TwentyFifthPayumWirecardCheckoutPage' => __DIR__ . '/Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}
