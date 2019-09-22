<?php


namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\Api\Ok;


use Deozza\PhilarmonyApiTesterBundle\Service\TestAsserter;

class OkControllerTest extends TestAsserter
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
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/docs/entities'                                           , "status"=>200, 'out'=>'docsEntitiesGet']],
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/docs/entities/gear'                                      , "status"=>200, 'out'=>'docsEntitiesGearGet']],
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/docs/properties'                                         , "status"=>200, 'out'=>'docsPropertiesGet']],
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/docs/properties/name'                                    , "status"=>200, 'out'=>'docsPropertiesNameGet']],
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/docs/enumerations'                                       , "status"=>200, 'out'=>'docsEnumerationsGet']],
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/docs/enumerations/boolean'                               , "status"=>200, 'out'=>'docsEnumerationsBooleanGet']],
                ["kind"=>"unit", "test"=>['method'=>'POST' , 'url'=>'/api/entities/gear'            , 'token'=>'#user_1#', "status"=>201, 'out'=>'entityPosted', 'in'=>'entityPost']],

            ];
    }
}