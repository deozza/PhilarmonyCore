<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\Api\NotFound;

use Deozza\PhilarmonyApiTesterBundle\Service\TestAsserter;

class NotFoundControllerTest extends TestAsserter
{
    const TEST_DATABASE_PATH = __DIR__ . "/../../../var/db_test/philarmony-core-test";

    public function setUp()
    {
        parent::setTestDatabasePath(self::TEST_DATABASE_PATH);
        parent::setEnv(json_decode(file_get_contents(__DIR__ . "/../../../DataFixtures/MongoDB/env.json"), true));
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
                ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/docs/entities/foo'                            , "status"=>404]],
                ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/docs/properties/foo'                          , "status"=>404]],
                ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/docs/enumerations/foo'                        , "status"=>404]],

                ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entities/foo'                                 , "status"=>404]],
                ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/foo'                                 , "status"=>404]],

                ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entities/00100000-0000-4000-a000-000000000000', "status"=>404]],
                ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/entities/00100000-0000-4000-a000-000000000000', "status"=>404]],
                ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entities/00100000-0000-4000-a000-000000000000', "status"=>404]],

            ];
    }
}