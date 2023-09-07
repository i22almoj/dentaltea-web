<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\Type\RegisterType;
use App\Form\Type\UserType;
use App\Form\Type\UserEditType;
use App\Form\Type\UserNewType;
use App\Form\Type\ChangePassType;
use App\Form\Type\ResetPassType;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Session\Session;


class UserController extends AbstractController
{
    private $slugger;
	private $repository;
	private $doctine;
	private $tokenStorage;
	
	public function __construct(ManagerRegistry $doctrine, SluggerInterface $slugger, TokenStorageInterface $tokenStorage){
		$this->slugger = $slugger;
		$this->doctrine = $doctrine;
		$this->repository = $doctrine->getRepository(User::class);
		$this->tokenStorage = $tokenStorage;
	}

/* 
	name: login
	description: Controls access to the login form
	path: /acceder
*/	
	public function login(AuthenticationUtils $autenticationUtils)
    { 
        $error = ''; $lastUsername = '';
        $error = $autenticationUtils->getLastAuthenticationError();
		
		$lastUsername = $autenticationUtils->getLastUsername();
		
		return $this->render('login.html.twig', array(
			'error' => $error,
			'last_username' => $lastUsername
		));    
    }

/* 
	name: register
	description: Controls access to the registration form
	path: /registro
*/
	public function register(Request $request, UserPasswordHasherInterface $hasher)
    {
		// Crear formulario
		$user = new User();
		$user->setCreationTime(new \Datetime('now'));
		$user->setActive(1);
		
		$form = $this->createForm(RegisterType::class, $user);
		
		$form->handleRequest($request);
				
		if($form->isSubmitted() && $form->isValid()){
			
			$user->setCreationTime(new \Datetime('now'));

			$hashed = $hasher->hashPassword($user, $user->getPassword());
			$user->setPassword($hashed);
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();
			
            $this->addFlash(
                'notice',
                'Registro completado. <a href="./.."><strong>Volver para acceder</strong></a>'
            );

			return $this->redirectToRoute('register');
		}
		
        return $this->render('register.html.twig', [
			'form' => $form->createView()
        ]);
    }

/* 
	name: register
	description: Controls access to the profile page
	path: /mi-cuenta
*/	
	public function profile(Request $request, UserPasswordHasherInterface $hasher, UserInterface $user, SluggerInterface $slugger)
    {
		
		$form = $this->createForm(UserType::class, $user);
		
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()){
			
            $em = $this->getDoctrine()->getManager();			
			$em->persist($user);
			$em->flush();

            $this->addFlash(
                'notice',
                'Cambios guardados correctamente.'
            );

			return $this->redirectToRoute('profile');
		}
		
		$form_pass = $this->createForm(ChangePassType::class, $user);
		$form_pass->handleRequest($request);
		

		if($form_pass->isSubmitted() && $form_pass->isValid()){
			
			$hashed = $hasher->hashPassword($user, $user->getPassword());
			$user->setPassword($hashed);
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();
			
            $this->addFlash(
                'notice_pass',
                'Contraseña cambiada correctamente.'
            );

			return $this->redirectToRoute('profile');
		}
		
        return $this->render('profile.html.twig', [
			'form' => $form->createView(),
			'form_pass' => $form_pass->createView(),
			'user' => $user
        ]);
    }

/* 
	name: deleteAccount
	description: Controls access to delete de user account
	path: /eliminar-cuenta
*/		
	public function deleteAccount(UserInterface $currentUser, Request $request, Security $security, TokenStorageInterface $tokenStorage): Response
	{
		$security->getUser()->eraseCredentials();
        $this->tokenStorage->setToken(null);

		$this->doctrine->getManager()->remove($currentUser);
		$this->doctrine->getManager()->flush();

		return $this->redirectToRoute('logout');
	}

/* 
	name: resetpass
	description: Controls access to the reset password form
	path: /recuperar-contrasena
*/		
	public function resetpass(Request $request, UserPasswordHasherInterface $hasher, MailerInterface $mailer)
    {
		if(!empty($_GET['c'])) 
			return $this->resetpass2($request, $hasher);
		
		$em = $this->getDoctrine()->getManager();
		$user_repo = $em->getRepository(User::class);
	
		$form = $this->createForm(ResetPassType::class);
		
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid()){
			$found = $user_repo->findOneByEmail($form->get('email')->getData());
			if(empty($found)){
				$this->addFlash(
					'warning',
					'El email '.$form->get('email')->getData().' no está asociado a ninguna cuenta de usuario'
				);
			}else{
				$code = $this->getResetPasswordCode($found);
				$url = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath().$this->generateUrl($request->attributes->get('_route'));
				$url .= '?c='.$code;
				$this->addFlash(
					'notice',
					'Hemos enviado un correo electrónico a tu dirección '.$form->get('email')->getData().'. Abre tu buzón de correo para continuar.'
				);

				$email = (new TemplatedEmail())
					->to($found->getEmail())
					->htmlTemplate('emails/base.html.twig')
					->subject('Recuperar Contraseña')
					->context([
						'subject' => 'DentalTEA',
						'title' => 'Recuperar contraseña',
						'content' => '<p>Si deseas recuperar tu contraseña, haz clic en el siguiente enlace:</p>',
						'button_text' => 'Cambiar contraseña',
						'button_url' => $url,
						'base_url' => $request->getScheme() . '://'.$request->getHttpHost() . $request->getBasePath()
					]);

				$mailer->send($email);
				
			}
		}

        return $this->render('resetpass.html.twig', [
			'form' => $form->createView()
        ]);
    }

/* 
	name: resetpass2
	description: Controls the second step of the reset password form
*/	
	private function resetpass2(Request $request, UserPasswordHasherInterface $hasher)
    {
		if(empty($_GET['c'])) 	return $this->redirectToRoute('resetpass');
		$code = $_GET['c'];
		$user = $this->checkResetPasswordCode($code);

		if(empty($user)){
			$this->addFlash(
				'warning',
				'El enlace ha caducado o es incorrecto'
			);
			return $this->render('resetpass.html.twig', []);

		}else{
			$form = $this->createForm(ChangePassType::class, $user);
			$form->handleRequest($request);
		
			if($form->isSubmitted() && $form->isValid()){
				
				$hashed = $hasher->hashPassword($user, $user->getPassword());
				$user->setPassword($hashed);
				
				$em = $this->getDoctrine()->getManager();
				$em->persist($user);
				$em->flush();
				
				$this->addFlash(
					'notice',
					'Contraseña cambiada correctamente. <a href="./.."><strong>Volver para acceder</strong></a>'
				);
				return $this->render('resetpass.html.twig', [
					
				]);
			}else{
				return $this->render('resetpass.html.twig', [
					'form' => $form->createView()
				]);
			}

			
		}  
    }

/* 
	name: getResetPasswordCode
	description: Returns a password recovery code
*/	
	private function getResetPasswordCode(User $user){
		$token = $this->getParameter('token');
		$string = json_encode(array(
			'token' => $token,
			'id' => $user->getId(),
			'email' => $user->getEmail(),
			'old_password' => $user->getPassword(),
			'ts' => strtotime(date('Y-m-d'))
			)
		);
		return base64_encode($string);
	}

/* 
	name: checkResetPasswordCode
	description: Validates a code to reset password
*/		
	private function checkResetPasswordCode($code){
		$string = base64_decode($code);
		
		if(!is_string($string) || !is_array(json_decode($string, true)) || (json_last_error() != JSON_ERROR_NONE))	
			return false;
		
		$data = (array) json_decode($string);
		
		if(empty($data['token'])||empty($data['id'])||empty($data['email'])||empty($data['old_password'])||empty($data['ts']))	
			return false;

		$token = $this->getParameter('token');
		if($token!=$data['token']) return false;

		$em = $this->getDoctrine()->getManager();
		$user_repo = $em->getRepository(User::class);

		$user = $user_repo->findOneById(intval($data['id']));
		$ts = strtotime(date('Y-m-d'));
		
		if(empty($user))	
			return false;
		if($data['email'] != $user->getEmail() || $data['old_password'] != $user->getPassword() || $data['ts'] != $ts )
			return false;

		return $user;
	}

/* 
	name: privacyPolicy
	description: Controls access to the PrivacyPolicy page
	path: /registro
*/
public function privacyPolicy(Request $request, UserPasswordHasherInterface $hasher)
{
	$content = $this->renderView('/parts/privacy.html.twig', []);

	return $this->render('privacy-page.html.twig', [
		'content' => $content
	]);
}	
}
