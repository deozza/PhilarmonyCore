<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\Forbidden;

use Deozza\PhilarmonyApiTesterBundle\Service\TestAsserter;

class MongodbForbiddenControllerTest extends TestAsserter
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
            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entity/00100000-0000-4000-a000-000000000000'      , "status"=>403]],
            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entity/00100000-0000-4000-a000-000000000000'      , "status"=>403, 'token'=>'token_userForbidden']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/entity/00100000-0000-4000-a000-000000000000'      , "status"=>403, 'token'=>'token_userForbidden']],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entity/00100000-0000-4000-a000-000000000000'      , "status"=>403, 'token'=>'token_userForbidden']],

            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entity/00500000-0000-4000-a000-000000000000/photo', "status"=>403]],
            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entity/00500000-0000-4000-a000-000000000000/photo', "status"=>403, 'token'=>'token_userForbidden']],

            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/validate/00500000-0000-4000-a000-000000000000'    , "status"=>403, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/retrograde/00400000-0000-4000-a000-000000000000'  , "status"=>403, 'token'=>'token_userActive']],
        ];
    }
}