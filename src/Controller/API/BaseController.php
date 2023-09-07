<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\UserSession;
use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
    /**
    * @var Security
    */
    
    public $headers;
    public $doctrine;
    public $jwtEncoder;
    public $JWTManager;
    public $userRepository;
    public $mailer;

    public function __construct(ManagerRegistry $doctrine, JWTEncoderInterface $jwtEncoder, JWTTokenManagerInterface $JWTManager, MailerInterface $mailer) 
    {
        $this->doctrine = $doctrine;
        $this->userRepository = $doctrine->getRepository(User::class);
        $this->jwtEncoder = $jwtEncoder;
        $this->JWTManager = $JWTManager;
        $this->mailer = $mailer;
        $this->headers = ['Access-Control-Allow-Origin' => '*'];
	}

/* 
	name: getTokenUser
	description: Gets the data of a user associated with a given token
*/      
    public function getTokenUser(Request $request)
    {
    	try {
			$token = str_replace('Bearer ', '', $request->headers->get('Authorization'));
            $payload =  $this->jwtEncoder->decode($token);
            if(empty($payload['email']))    return false;
        } catch (\Exception $e) {
            return false;
        }

        $user = $this->userRepository->findOneBy(['email' => $payload['email']]);
          
        if (empty($user)|| !($user instanceof User) ) {
            return false;
        }

        return $user;
    }

/* 
	name: getUserToken
	description: Gets a token associated with a user account
*/   
    public function getUserToken(UserInterface $user): JsonResponse
    { 
        return new JsonResponse(['session_id' => $this->JWTManager->create($user)]);
    }

}