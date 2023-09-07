<?php

namespace App\Controller\API;

use App\Controller\API\BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Entity\User;
use App\Repository\UserRepository;


class UserController extends BaseController
{

/* 
	name: index
	description: Gets the data of the current user account
	path: /api/user
*/    
    public function index(Request $request){
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);

        return new JsonResponse(['success' => true, 'msg' => 'Datos de usuario', 'data' => ['id' =>$user->getId(), 'name' =>$user->getName(), 'email' =>$user->getEmail()]  ], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: edit
	description: Updates the data of the current user account
	path: /api/user/edit
*/        
    public function edit(Request $request): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);

        $params = json_decode($request->getContent(), true); 

        if(empty($params['name'])){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir un nombre'], Response::HTTP_BAD_REQUEST, $this->headers);
        }else{
            $user->setName($params['name']);
        }

        if(empty($params['email'])){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir un email'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['success' => false, 'msg' => 'El email introducido no es correcto'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $user_email_exists = $this->doctrine->getRepository(User::class)->findOneBy(array('email' => $params['email']));
        if(!empty($user_email_exists)&&$user_email_exists->getId()!=$user->getId()){
            return new JsonResponse(['success' => false, 'msg' => 'Ya existe una cuenta registrada con esta dirección de email'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $user->setEmail($params['email']);

        $this->doctrine->getManager()->persist($user);
        $this->doctrine->getManager()->flush();

        return new JsonResponse(['success' => true, 'msg' => 'Cuenta actualizada correctamente', 'data' => $params], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: register
	description: Register a new user account
	path: /api/user/register
*/    
    public function register(Request $request, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $params = json_decode($request->getContent(), true); 

        if(empty($params['name'])){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir un nombre'], Response::HTTP_BAD_REQUEST, $this->headers);
        }
        
        if(empty($params['email'])){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir un email'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['success' => false, 'msg' => 'El email introducido no es correcto'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $user_email_exists = $this->doctrine->getRepository(User::class)->findOneBy(array('email' => $params['email']));
        if(!empty($user_email_exists)){
            return new JsonResponse(['success' => false, 'msg' => 'Ya existe una cuenta registrada con esta dirección de email'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        if(empty($params['password'])){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir una contraseña'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        if(strlen($params['password'])<6){
            return new JsonResponse(['success' => false, 'msg' => 'Las contraseña debe tener al menos 6 caracteres'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $user = new User();
        $user->setCreationTime(new \Datetime('now'));
		$user->setActive(1);

        $user->setName($params['name']);
        $user->setEmail($params['email']);
        
        $hashed = $hasher->hashPassword($user, $params['password']);
        $user->setPassword($hashed);

        $this->doctrine->getManager()->persist($user);
        $this->doctrine->getManager()->flush();

        return new JsonResponse(['success' => true, 'msg' => 'Cuenta creada correctamente', 'data' => ['id' =>$user->getId(), 'name' =>$user->getName(), 'email' =>$user->getEmail()] ], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: changePassword
	description: Changes the password of the current user account
	path: /api/user/change-password
*/    
    public function changePassword(Request $request, UserPasswordHasherInterface $hasher): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);

        $params = json_decode($request->getContent(), true); 

        if(empty($params['password'])){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir una contraseña'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        if(strlen($params['password'])<6){
            return new JsonResponse(['success' => false, 'msg' => 'La contraseña debe tener al menos 6 caracteres'], Response::HTTP_BAD_REQUEST, $this->headers);
        }
        
        $hashed = $hasher->hashPassword($user, $params['password']);
        $user->setPassword($hashed);
        
        $this->doctrine->getManager()->persist($user);
        $this->doctrine->getManager()->flush();

        return new JsonResponse(['success' => true, 'msg' => 'Contraseña actualizada correctamente', 'data' => []], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: delete
	description: Deletes the current user account
	path: /api/user/delete
*/      
    public function delete(Request $request): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);

        $this->doctrine->getManager()->remove($user);	
		$this->doctrine->getManager()->flush();

        return new JsonResponse(['success' => true, 'msg' => 'Usuario eliminado correctamente', 'data' => []], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: resetpass
	description: Gets the query to recover the password
	path: /api/user/resetpass
*/     
    public function resetpass(Request $request, UserPasswordHasherInterface $hasher, MailerInterface $mailer): JsonResponse
    {
        $params = json_decode($request->getContent(), true); 

        if(empty($params['email'])){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir un email'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['success' => false, 'msg' => 'El email introducido no es correcto'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $user = $this->doctrine->getRepository(User::class)->findOneByEmail($params['email']);
        
        if(empty($user)){
            return new JsonResponse(['success' => false, 'msg' => 'El email '.$params['email'].' no está asociado a ninguna cuenta de usuario'], Response::HTTP_BAD_REQUEST, $this->headers);
        }else{
            $token = $this->getParameter('token');
		    $string = json_encode(array(
                'token' => $token,
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'old_password' => $user->getPassword(),
                'ts' => strtotime(date('Y-m-d'))
			    )
		    );
		    $code = base64_encode($string);
            $url = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath().'/recuperar-contrasena/';
            $url .= '?c='.$code;
            $email = (new TemplatedEmail())
                ->to($user->getEmail())
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

            return new JsonResponse(['success' => true, 'msg' => 'Hemos enviado un correo electrónico con la información para recuperar tu contraseña', 'data' => []], Response::HTTP_CREATED, $this->headers);
        }
    }

}