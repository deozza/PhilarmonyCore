<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\DataFixtures\MongoDB;

use Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Document\ApiToken;
use Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Document\User;

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

        $user = new User();
        $user->setUsername($name);
        $user->setEmail($name.'@mail.com');
        $user->setRegisterDate(new \DateTime('now'));
        $user->setActive($active);

        $user->setPassword('');
        $user->setRoles($role);
        $user->setUuid();

        $this->manager->persist($user);

        $this->createTokenForUser($user);

        return $user;
    }

    public function createTokenForUser(User $user)
    {
        $tokenValue = "token_".$user->getUsername();
        $token = new ApiToken($user, $tokenValue);
        $this->manager->persist($token);
    }
}