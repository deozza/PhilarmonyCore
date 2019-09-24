<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\Api\Unauthorized;

use Deozza\PhilarmonyApiTesterBundle\Service\TestAsserter;

class ForbiddenControllerTest extends TestAsserter
{
    const TEST_DATABASE_PATH = __DIR__ . "/../../../var/db_test/philarmony-core-test";

    public function setUp()
    {
        parent::setTestDatabasePath(self::TEST_DATABASE_PATH);
        parent::setEnv(json_decode(file_get_contents(__DIR__ . "/../../../src/DataFixtures/MongoDB/env.json"), true));
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

                ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entities/#gear_6#', 'token'=>'token_userActive', "status"=>403]],
                ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/entities/#gear_6#', 'token'=>'token_userActive', "status"=>403]],
            ];
    }
}