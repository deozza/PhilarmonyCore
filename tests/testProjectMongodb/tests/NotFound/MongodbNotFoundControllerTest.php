<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\NotFound;

use Deozza\PhilarmonyApiTesterBundle\Service\TestAsserter;

class MongodbNotFoundControllerTest extends TestAsserter
{
    const TEST_DATABASE_PATH = __DIR__."/../../var/db_test/philarmony-core-test";

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
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/invalid'                                     , "status"=>404, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entities/invalid'                                   , "status"=>404, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entity/00300000-0000-4000-a000-000000000001'        , "status"=>404, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/entity/00300000-0000-4000-a000-000000000001'        , "status"=>404, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entity/00300000-0000-4000-a000-000000000001'        , "status"=>404, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/00300000-0000-4000-a000-000000000001/photo'  , "status"=>404, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/00300000-0000-4000-a000-000000000000/invalid', "status"=>404, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entity/00300000-0000-4000-a000-000000000001/photo'  , "status"=>404, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entity/00300000-0000-4000-a000-000000000000/invalid', "status"=>404, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entity/00300000-0000-4000-a000-000000000001/photo'  , "status"=>404, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entity/00300000-0000-4000-a000-000000000000/invalid', "status"=>404, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/validate/00300000-0000-5000-a000-000000000001'     , "status"=>404, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/validate/00400000-0000-4000-a000-000000000000'     , "status"=>404, 'token'=>'token_userAdmin']],

            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/retrograde/00300000-0000-4000-a000-000000000001'   , "status"=>404, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/retrograde/00400000-0000-5000-a000-000000000000'   , "status"=>404, 'token'=>'token_userAdmin']],

            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/doc/entity/invalid'                                , "status"=>404, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/doc/property/invalid'                              , "status"=>404, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/doc/enumeration/invalid'                           , "status"=>404, 'token'=>'token_userActive']],
        ];
    }
}