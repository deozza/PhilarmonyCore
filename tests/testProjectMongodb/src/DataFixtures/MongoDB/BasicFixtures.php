<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\DataFixtures\MongoDB;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class BasicFixtures extends Fixture
{
    use UserFixtureTrait;

    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->users = $this->createUsers(
            [
                ['name'=>'userActive', 'active'=>true, 'role'=>[]],
                ['name'=>'userInactive', 'active'=>false, 'role'=>[]],
                ['name'=>'userForbidden', 'active'=>true, 'role'=>[]],
                ['name'=>'userAdmin', 'active'=>true, 'role'=>["ROLE_ADMIN"]],
                ['name'=>'userActive2', 'active'=>true, 'role'=>[]]
            ]);
        $this->manager->flush();

        $this->manager->flush();
        $env = [];
        $i = 1;
        foreach($this->users as $user)
        {
            $env['user_'.$i] = $user->getUuidAsString();
            $i++;
        }


        file_put_contents(__DIR__.'/env.json', json_encode($env, JSON_PRETTY_PRINT));
    }
}
