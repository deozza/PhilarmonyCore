<?php


namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\Api\InvalidMethod;


use Deozza\PhilarmonyApiTesterBundle\Service\TestAsserter;

class InvalidMethodConrollerTest extends TestAsserter
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
                ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/docs/entities'                                           , "status"=>405]],
                ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/docs/entities'                                           , "status"=>405]],
                ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/docs/entities'                                           , "status"=>405]],

                ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/docs/entities/gear'                                      , "status"=>405]],
                ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/docs/entities/gear'                                      , "status"=>405]],
                ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/docs/entities/gear'                                      , "status"=>405]],

                ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/docs/properties'                                         , "status"=>405]],
                ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/docs/properties'                                         , "status"=>405]],
                ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/docs/properties'                                         , "status"=>405]],

                ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/docs/properties/name'                                    , "status"=>405]],
                ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/docs/properties/name'                                    , "status"=>405]],
                ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/docs/properties/name'                                    , "status"=>405]],

                ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/docs/enumerations'                                       , "status"=>405]],
                ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/docs/enumerations'                                       , "status"=>405]],
                ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/docs/enumerations'                                       , "status"=>405]],

                ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/docs/enumerations/boolean'                               , "status"=>405]],
                ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/docs/enumerations/boolean'                               , "status"=>405]],
                ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/docs/enumerations/boolean'                               , "status"=>405]],

            ];
    }
}