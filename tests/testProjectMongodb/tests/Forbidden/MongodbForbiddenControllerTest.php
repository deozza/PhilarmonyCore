<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\Forbidden;

use Deozza\PhilarmonyApiTesterBundle\Service\TestAsserter;

class MongodbForbiddenControllerTest extends TestAsserter
{
    const TEST_DATABASE_PATH = __DIR__."/../../var/db_test/philarmony-core-test";

    public function setUp()
    {
        parent::setTestDatabasePath(self::TEST_DATABASE_PATH);
        $this->setEnv(json_decode(file_get_contents(__DIR__.'/../../src/DataFixtures/MongoDB/env.json'), true));
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
            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entity/#annonce_6#'      , "status"=>403]],
            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entity/#annonce_6#'      , "status"=>403, 'token'=>'token_userForbidden']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/entity/#annonce_6#'      , "status"=>403, 'token'=>'token_userForbidden']],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entity/#annonce_6#'      , "status"=>403, 'token'=>'token_userForbidden']],

            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entity/#annonce_10#/file/photo', "status"=>403]],
            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entity/#annonce_10#/file/photo', "status"=>403, 'token'=>'token_userForbidden']],

            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/validate/#annonce_10#'    , "status"=>403, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/retrograde/#annonce_9#'  , "status"=>403, 'token'=>'token_userActive']],
        ];
    }
}