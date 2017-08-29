<?php
/**
 * 25th-floor GmbH
 *
 * @link https://www.25th-floor.com
 * @copyright Copyright (c) 2017 25th-floor GmbH
 */

declare(strict_types=1);

namespace TwentyFifthTest\Payum\WirecardCheckoutPage\Action;


use Payum\Core\Model\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use PHPUnit\Framework\TestCase;
use TwentyFifth\Payum\WirecardCheckoutPage\Action\StatusAction;

class StatusActionTest extends TestCase
{
    private $action;

    protected function setUp()
    {
        $this->action = new StatusAction();
    }

    public function testExecuteMarkCancelled()
    {
        $request = $this->getMockBuilder(GetStatusInterface::class)
            ->getMock();

        $model = new ArrayObject();
        $model['CANCELLED'] = 1;

        $request
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model));

        $request
            ->expects($this->once())
            ->method('markCanceled');

        $this->action->execute($request);
    }

    public function testExecuteMarkCaptured()
    {
        $request = $this->getMockBuilder(GetStatusInterface::class)
            ->getMock();

        $model = new ArrayObject();
        $model['CANCELLED'] = 1;

        $request
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model));

        $request
            ->expects($this->once())
            ->method('markCanceled');

        $this->action->execute($request);
    }
}