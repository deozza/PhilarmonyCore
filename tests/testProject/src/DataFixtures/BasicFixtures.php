<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class BasicFixtures extends Fixture
{
    use UserFixtureTrait;
    use AnnonceFixtureTrait;

    private $manager;
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

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

        $this->annonces = $this->createAnnonces(
            [
                ["owner" => $this->users[0], "validationState"=>"posted", "photo"=>null],
                ["owner" => $this->users[0], "validationState"=>"published", "photo"=>null],
                ["owner" => $this->users[2], "validationState"=>"published", "photo"=>null],
                ["owner" => $this->users[2], "validationState"=>"published", "photo"=>"1.jpeg"],
                ["owner" => $this->users[0], "validationState"=>"posted", "photo"=>"1.jpeg"],

            ]
        );
        $this->manager->flush();
    }


}
