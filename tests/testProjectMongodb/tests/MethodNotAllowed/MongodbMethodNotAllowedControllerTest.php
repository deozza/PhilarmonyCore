<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\MethodNotAllowed;

use Deozza\PhilarmonyApiTesterBundle\Service\TestAsserter;

class MongodbMethodNotAllowedControllerTest extends TestAsserter
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
            ["kind"=>"unit", "test"=>['method'=>'PUT'   , 'url'=>'/api/entities/annonce'                                , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entities/annonce'                                , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/entities/annonce'                                , "status"=>405, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'PUT'   , 'url'=>'/api/entity/#annonce_8#'     , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/#annonce_8#'     , "status"=>405, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'PUT'   , 'url'=>'/api/entity/annonce'                                  , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/entity/annonce'                                  , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/entity/annonce'                                  , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entity/annonce'                                  , "status"=>405, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/validate/#annonce_8#'   , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/validate/#annonce_8#'   , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/validate/#annonce_8#'   , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PUT'   , 'url'=>'/api/validate/#annonce_8#'   , "status"=>405, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/retrograde/#annonce_8#' , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/retrograde/#annonce_8#' , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'GET'   , 'url'=>'/api/retrograde/#annonce_8#' , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PUT'   , 'url'=>'/api/retrograde/#annonce_8#' , "status"=>405, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/entity/#annonce_8#/file/photo', "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PUT'   , 'url'=>'/api/entity/#annonce_8#/file/photo', "status"=>405, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'PUT'   , 'url'=>'/api/doc/entities'                                    , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/doc/entities'                                    , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/doc/entities'                                    , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/doc/entities'                                    , "status"=>405, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'PUT'   , 'url'=>'/api/doc/entity/annonce'                              , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/doc/entity/annonce'                              , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/doc/entity/annonce'                              , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/doc/entity/annonce'                              , "status"=>405, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'PUT'   , 'url'=>'/api/doc/properties'                                  , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/doc/properties'                                  , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/doc/properties'                                  , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/doc/properties'                                  , "status"=>405, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'PUT'   , 'url'=>'/api/doc/property/title'                              , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/doc/property/title'                              , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/doc/property/title'                              , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/doc/property/title'                              , "status"=>405, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'PUT'   , 'url'=>'/api/doc/enumerations'                                , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/doc/enumerations'                                , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/doc/enumerations'                                , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/doc/enumerations'                                , "status"=>405, 'token'=>'token_userActive']],

            ["kind"=>"unit", "test"=>['method'=>'PUT'   , 'url'=>'/api/doc/enumeration/boolean'                         , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/doc/enumeration/boolean'                         , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/doc/enumeration/boolean'                         , "status"=>405, 'token'=>'token_userActive']],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/doc/enumeration/boolean'                         , "status"=>405, 'token'=>'token_userActive']],
        ];
    }
}