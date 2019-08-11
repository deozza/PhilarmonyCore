<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMysql\src\Security;

use Deozza\PhilarmonyCoreBundle\Tests\testProjectMysql\src\Entity\User;
use Deozza\PhilarmonyCoreBundle\Tests\testProjectMysql\src\Repository\ApiTokenRepository;
use Deozza\PhilarmonyCoreBundle\Tests\testProjectMysql\src\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyUserProvider implements UserProviderInterface
{
    public function __construct(ApiTokenRepository $apiTokenRepository, UserRepository $userRepository)
    {
        $this->apiTokenRepository = $apiTokenRepository;
        $this->userRepository = $userRepository;
    }

    public function getAuthToken($authTokenHeader)
    {
        return $this->apiTokenRepository->findOneByValue($authTokenHeader);
    }

    public function loadUserByUsername($username)
    {
        return $this->userRepository->findByUsername($username);
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}