<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\tests\NotAuthorized;

use Deozza\ApiTesterBundle\Service\TestAsserter;

class NotAuthorizedControllerTest extends TestAsserter
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
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/annonce'                                   , "status"=>401]],

            ["kind"=>"unit", "test"=>['method'=>'PATCH' , 'url'=>'/api/entity/00300000-0000-4000-a000-000000000000'      , "status"=>401]],

            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entity/00300000-0000-4000-a000-000000000000'      , "status"=>401]],

            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/entity/00400000-0000-4000-a000-000000000000/photo', "status"=>401]],
            ["kind"=>"unit", "test"=>['method'=>'DELETE', 'url'=>'/api/entity/00400000-0000-4000-a000-000000000000/photo', "status"=>401]],

            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/validate/00400000-0000-4000-a000-000000000000'    , "status"=>401]],
            ["kind"=>"unit", "test"=>['method'=>'POST'  , 'url'=>'/api/retrograde/00400000-0000-4000-a000-000000000000'  , "status"=>401]],
        ];
    }
}