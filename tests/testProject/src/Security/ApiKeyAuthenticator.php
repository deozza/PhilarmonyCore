<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Security;

use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Entity\ApiToken;
use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Firebase\JWT\JWT;
use Symfony\Component\Dotenv\Dotenv;

class ApiKeyAuthenticator extends AbstractGuardAuthenticator
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function supports(Request $request)
    {
        return true;
    }

    public function getCredentials(Request $request)
    {

        $header = $request->headers->get('Authorization');
        return substr($header, 7);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $entity = new User();
        if(empty($credentials))
        {
            return $entity;
        }

        try
        {
            $env = new Dotenv();
            $env->load(__DIR__."/../../.env");
            $secret = getenv("APP_SECRET");
            $data = JWT::decode($credentials, $secret, ["HS256"]);
            $username = $data['username'];
            $user = $this->em->getRepository(User::class)->findOneByUsername($username);
            if(!$this->em->getRepository(ApiToken::class)->findOneByUser($user))
            {
                throw new CustomUserMessageAuthenticationException("Invalid token");
            }

            if($user->getActive() === false)
            {
                throw new CustomUserMessageAuthenticationException("Your account is not active.");
            }

            return $user;
        }
        catch(\Exception $e)
        {
            $data = $this->em->getRepository(ApiToken::class)->findOneByToken($credentials);
            if($data)
            {

                if($data->getUser()->getActive() === false)
                {
                    throw new CustomUserMessageAuthenticationException("Your account is not active.");
                }

                return $data->getUser();
            }
            else
            {
                throw new CustomUserMessageAuthenticationException("Invalid token");
            }
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            "message" => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => 'Authentication required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
