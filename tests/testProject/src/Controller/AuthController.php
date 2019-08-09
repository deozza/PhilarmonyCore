<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Controller;

use Deozza\ResponseMakerBundle\Service\FormErrorSerializer;
use Deozza\ResponseMakerBundle\Service\ResponseMaker;
use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Form\user\CredentialsType;
use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Document\ApiToken;
use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Document\Credentials;
use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Repository\ApiTokenRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * User controller.
 *
 * @Route("api/")
 */
class AuthController extends AbstractController
{
    public function __construct(DocumentManager $dm, ResponseMaker $responseMaker, FormErrorSerializer $serializer)
    {
        $this->dm = $dm;
        $this->response = $responseMaker;
        $this->serializer = $serializer;
    }

    /**
     * @Route("tokens", name="post_auth_token", methods={"POST"})
     */
    public function postTokenAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $credentials = new Credentials();
        $form = $this->createForm(CredentialsType::class, $credentials);
        $postedCredentials = json_decode($request->getContent(), true);
        $form->submit($postedCredentials);
        if(!$form->isValid())
        {
            return $this->response->badRequest($this->serializer->convertFormToArray($form));
        }
        $repository = $this->dm->getRepository($this->userEntity);

        $user = $repository->findByUsernameOrEmail($credentials->getLogin(), $credentials->getLogin());

        if(empty($user) || $user[0]->getActive() == false)
        {
            return $this->response->badRequest("Invalid credentials");
        }
        $user = $user[0];

        $isPasswordValid= $encoder->isPasswordValid($user, $credentials->getPassword());

        if(!$isPasswordValid)
        {
            $user->setLastFailedLogin(new \DateTime('now'));
            $this->dm->persist($user);
            $this->dm->flush();
            return $this->response->badRequest("Invalid credentials");
        }

        $env = new Dotenv();
        $env->load($this->getParameter("kernel.project_dir")."/.env");
        $secret = getenv("APP_SECRET");
        $token = ["username" => $user->getUsername(), "exp"=> date_create("+1 day")->format('U')];

        $user->setLastLogin(new \DateTime('now'));
        $this->dm->persist($user);

        $this->dm->flush();
        return $this->response->created(JWT::encode($token, $secret));
    }
}
