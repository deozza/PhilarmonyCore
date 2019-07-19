<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\tests\Conflict;

use Deozza\ApiTesterBundle\Service\TestAsserter;

class ConflictControllerTest extends TestAsserter
{
    public function setUp()
    {
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
            //["kind"=>"unit", "test"=>['method'=>'POST' , 'url'=>'/api/entity/annonce'                            , "status"=>409, 'token'=>'token_userActive', 'in'=>'postAnnonce', "out"=>"postedAnnonce"]],
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/retrograde/00400000-0000-4000-a000-000000000000'  , "status"=>409, "token"=>"token_userAdmin"]],

        ];
    }
}