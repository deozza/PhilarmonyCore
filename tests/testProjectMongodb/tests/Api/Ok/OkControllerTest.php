<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\Api\Ok;

use Deozza\PhilarmonyApiTesterBundle\Service\TestAsserter;

class OkControllerTest extends TestAsserter
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
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/docs/entities'            , "status"=>200, 'out'=>'docsEntitiesGet']],
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/docs/entities/gear'       , "status"=>200, 'out'=>'docsEntitiesGearGet']],
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/docs/properties'          , "status"=>200, 'out'=>'docsPropertiesGet']],
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/docs/properties/name'     , "status"=>200, 'out'=>'docsPropertiesNameGet']],
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/docs/enumerations'        , "status"=>200, 'out'=>'docsEnumerationsGet']],
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/docs/enumerations/boolean', "status"=>200, 'out'=>'docsEnumerationsBooleanGet']],

                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/entities/gear'            , "status"=>200, 'out'=>'entitiesGetList']],
                ["kind"=>"unit", "test"=>['method'=>'GET'  , 'url'=>'/api/entities/#gear_6#'        , "status"=>200, 'out'=>'entitiesGetSpecific']],

                ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/gear'             , 'token'=>'token_userAdmin', "status"=>201, 'out'=>'entityPosted', 'in'=>'entityPost']],

                ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/entities/#gear_6#'       , 'token'=>'token_userAdmin', "status"=>200, 'out'=>'entityPatched', 'in'=>'entityPatch']],

                ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entities/#gear_6#'       , 'token'=>'token_userAdmin', "status"=>204]],

                [
                    "kind"=>"scenario",
                    "test"=>[
                        ['method'=>'POST' , 'url'=>'/api/entity/character'                                   , 'token'=>'token_userActive', "status"=>201, 'out'=>"characterScenarioPosted"      , 'in'=>'characterScenarioPost'],
                        ['method'=>'GET'  , 'url'=>'/api/entities/#character_uuid#'                          , 'token'=>'token_userActive', "status"=>200, 'out'=>'characterScenarioGet'],
                        ['method'=>'GET'  , 'url'=>'/api/entities/#character_uuid#'                          , 'token'=>'token_userAdmin' , "status"=>200, 'out'=>'characterScenarioGet'],
                        ['method'=>'POST' , 'url'=>'/api/entities/#character_uuid#/embedded/character_naming', 'token'=>'token_userActive', "status"=>201, 'out'=>"characterNamingScenarioPosted", 'in'=>'characterNamingScenarioPost'],
                        ['method'=>'GET'  , 'url'=>'/api/entities/#character_uuid#'                          , 'token'=>'token_userActive', "status"=>200, 'out'=>'characterScenarioGet2'],
                        ['method'=>'GET'  , 'url'=>'/api/entities/#character_uuid#'                          , 'token'=>'token_userAdmin' , "status"=>200, 'out'=>'characterScenarioGet2'],
                        ['method'=>'GET'  , 'url'=>'/api/entities/#character_uuid#'                                                       , "status"=>403],
                        ['method'=>'PATCH', 'url'=>'/api/entities/#character_uuid#/validation_state'                                      , "status"=>401],
                        ['method'=>'PATCH', 'url'=>'/api/entities/#character_uuid#/validation_state'         , 'token'=>'token_userActive', "status"=>403                                        , 'in'=>'characterScenarioStatePatch'],
                        ['method'=>'PATCH', 'url'=>'/api/entities/#character_uuid#/validation_state'         , 'token'=>'token_userAdmin' , "status"=>200, 'out'=>'characterScenarioStatePatched', 'in'=>'characterScenarioStatePatch'],
                        ['method'=>'GET'  , 'url'=>'/api/entities/#character_uuid#'                                                       , "status"=>200, 'out'=>'characterScenarioGet3'],
                        ['method'=>'PATCH', 'url'=>'/api/entities/#character_uuid#/validation_state'         , 'token'=>'token_userAdmin' , "status"=>200, 'out'=>'characterScenarioStatePatched2', 'in'=>'characterScenarioStatePatch2'],
                        ['method'=>'GET'  , 'url'=>'/api/entities/#character_uuid#'                                                       , "status"=>403],
                    ]
                ],
                [
                    "kind" => "scenario",
                    "test" => [
                        ['method'=>'POST' , 'url'=>'/api/entity/character'                             , 'token'=>'token_userActive', "status"=>201, 'out'=>"characterScenarioPosted"           , 'in'=>'characterScenarioPost'],
                        ['method'=>'POST' , 'url'=>'/api/entities/#character_uuid#/embedded/owned_gear', 'token'=>'token_userActive', "status"=>400, 'out'=>"characterGearScenarioInvalidPosted", 'in'=>'characterGearScenarioInvalidPost'],
                        ['method'=>'POST' , 'url'=>'/api/entities/#character_uuid#/embedded/owned_gear', 'token'=>'token_userActive', "status"=>201, 'out'=>"characterGearScenarioPosted"       , 'in'=>'characterGearScenarioPost'],
                        ['method'=>'GET'  , 'url'=>'/api/entities/#character_uuid#'                    , 'token'=>'token_userActive', "status"=>200, 'out'=>'characterGearScenarioGet'],
                    ]
                ]
            ];
    }
}