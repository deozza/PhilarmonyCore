<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\tests\BadRequest;

use Deozza\ApiTesterBundle\Service\TestAsserter;

class BadRequestControllerTest extends TestAsserter
{
    const TEST_DATABASE_PATH = __DIR__."/../../var/db_test/demo.sql";

    public function setUp()
    {
        parent::setTestDatabasePath(self::TEST_DATABASE_PATH);
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