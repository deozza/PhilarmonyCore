<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\tests\Conflict;

use Deozza\PhilarmonyApiTesterBundle\Service\TestAsserter;

class ConflictControllerTest extends TestAsserter
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
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/annonce'                                   , "status"=>409, "token"=>"token_userActive", "in"=>"postAnnonce", "out"=>"postedAnnonce"]],
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/retrograde/00400000-0000-4000-a000-000000000000'  , "status"=>409, "token"=>"token_userAdmin"]],

        ];
    }
}