<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\Api\BadRequest;

use Deozza\PhilarmonyApiTesterBundle\Service\TestAsserter;

class BadRequestControllerTest extends TestAsserter
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
                ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/gear'    , 'token'=>'token_userAdmin', "status"=>400, 'out'=>'entityPostedWithMissingField' , 'in'=>'entityPostWithMissingField']],
                ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/gear'    , 'token'=>'token_userAdmin', "status"=>400, 'out'=>'entityPostedWithExtraField'   , 'in'=>'entityPostWithExtraField']],
                ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/gear'    , 'token'=>'token_userAdmin', "status"=>400, 'out'=>'entityPostedWithTooLongField' , 'in'=>'entityPostWithTooLongField']],
                ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/gear'    , 'token'=>'token_userAdmin', "status"=>400, 'out'=>'entityPostedWithTooShortField', 'in'=>'entityPostWithTooShortField']],
            ];
    }
}