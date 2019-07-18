<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\tests\BadRequest;

use Deozza\ApiTesterBundle\Service\TestAsserter;

class BadRequestControllerTest extends TestAsserter
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @dataProvider addDataProvider
     */
    public function testUnit($kind, $test)
    {
        parent::launchTestByKind($kind, $test);
    }

    public function addDataProvider()
    {
        return
        [

        ];
    }
}