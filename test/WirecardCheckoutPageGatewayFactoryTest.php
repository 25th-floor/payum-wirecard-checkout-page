<?php
/**
 * 25th-floor GmbH
 *
 * @link https://www.25th-floor.com
 * @copyright Copyright (c) 2017 25th-floor GmbH
 */

declare(strict_types=1);

namespace TwentyFifthTest\Payum\WirecardCheckoutPage;


use Payum\Core\GatewayFactory;
use PHPUnit\Framework\TestCase;
use TwentyFifth\Payum\WirecardCheckoutPage\WirecardCheckoutPageGatewayFactory;

class WirecardCheckoutPageGatewayFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSubClassGatewayFactory()
    {
        $this->assertInstanceOf(GatewayFactory::class, new WirecardCheckoutPageGatewayFactory());
    }
}