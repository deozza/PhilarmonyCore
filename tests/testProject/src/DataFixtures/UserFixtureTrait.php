<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\DataFixtures;

use Deozza\PhilarmonyUserBundle\Entity\ApiToken;
use App\Entity\RealUser;

trait UserFixtureTrait
{

    public function createUsers(array $items)
    {
        $users = [];
        foreach($items as $item)
        {
            $user = $this->createUser($item["name"], $item['active'], $item['role']);
            $users[] = $user;
        }

        return $users;
    }

    public function createUser($name, $active, $role=[])
    {

        $user = new RealUser();
        $user->setUsername($name);
        $user->setEmail($name.'@mail.com');
        $user->setRegisterDate(new \DateTime('now'));
        $user->setActive($active);

        $encoded = $this->encoder->encodePassword($user, $name);

        $user->setPassword($encoded);
        $user->setRoles($role);

        $this->manager->persist($user);

        $this->createTokenForUser($user);

        return $user;
    }

    public function createTokenForUser(RealUser $user)
    {
        $tokenValue = "token_".$user->getUsername();
        $token = new ApiToken($user, $tokenValue);
        $this->manager->persist($token);
    }
}