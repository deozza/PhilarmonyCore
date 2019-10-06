<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\DataFixtures\MongoDB;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class BasicFixtures extends Fixture
{
    use UserFixtureTrait;
    use GearFixtureTrait;
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $env = [];
        $i = 1;

        $this->users = $this->createUsers(
            [
                ['name'=>'userActive', 'active'=>true, 'role'=>[]],
                ['name'=>'userInactive', 'active'=>false, 'role'=>[]],
                ['name'=>'userForbidden', 'active'=>true, 'role'=>[]],
                ['name'=>'userAdmin', 'active'=>true, 'role'=>["ROLE_ADMIN"]],
                ['name'=>'userActive2', 'active'=>true, 'role'=>[]]
            ]);
        $this->manager->flush();
        foreach($this->users as $user)
        {
            $env['user_'.$i] = $user->getUuidAsString();
            $i++;
        }
/*
        $this->gears = $this->createGears(
            [
                ["owner"=>$this->users[3],'name'=>"sword", "description"=>"Stick the pointy end"],
                ["owner"=>$this->users[3],'name'=>"shield", "description"=>"Block the others pointy end"],
            ]
        );
        $this->manager->flush();
        foreach($this->gears as $gear)
        {
            $env['gear_'.$i] = $gear->getUuidAsString();
            $i++;
        }
*/
        file_put_contents(__DIR__ . '/env.json', json_encode($env, JSON_PRETTY_PRINT));
    }
}
