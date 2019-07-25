<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\tests\NotAuthorized;

use Deozza\PhilarmonyApiTesterBundle\Service\TestAsserter;

class NotAuthorizedControllerTest extends TestAsserter
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
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/annonce'                                   , "status"=>401]],

            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/entity/00300000-0000-4000-a000-000000000000'      , "status"=>401]],

            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entity/00300000-0000-4000-a000-000000000000'      , "status"=>401]],

            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/00400000-0000-4000-a000-000000000000/photo', "status"=>401]],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entity/00400000-0000-4000-a000-000000000000/photo', "status"=>401]],

            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/validate/00400000-0000-4000-a000-000000000000'    , "status"=>401]],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/retrograde/00400000-0000-4000-a000-000000000000'  , "status"=>401]],
        ];
    }
}