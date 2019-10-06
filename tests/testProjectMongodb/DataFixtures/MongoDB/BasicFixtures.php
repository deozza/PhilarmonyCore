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

        $this->env = [];
        $this->i = 1;

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
            $this->env['user_'.$this->i] = $user->getUuidAsString();
            $this->i++;
        }
        $this->gears = $this->createGears(
            [
                ["owner"=>$this->users[3],'gear_properties'=>['name'=>"sword", "description"=>"Stick the pointy end"]],
                ["owner"=>$this->users[3],'gear_properties'=>['name'=>"shield", "description"=>"Block the others pointy end"]],
            ]
        );
        file_put_contents(__DIR__ . '/env.json', json_encode($this->env, JSON_PRETTY_PRINT));
    }
}
